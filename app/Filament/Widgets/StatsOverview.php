<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use App\Models\Customer;
use App\Helpers\BonusCalculator;
use Filament\Support\Colors\Color;
use Filament\Widgets\Widget;
use Carbon\Carbon;

class StatsOverview extends Widget
{
    protected static ?int $sort = 1;

    protected static string $view = 'filament.widgets.stats-overview';

    // Help override column span of the widget to take full-width
    protected int | string | array $columnSpan = 'full';

    protected function getViewData(): array
    {
        Carbon::setLocale('id');

        // 1. Core aggregates
        $totalPiutang = 0.00;
        $piutangCount = 0;
        
        $piutangTransactions = Transaction::where('status', 'Piutang')->get();
        foreach ($piutangTransactions as $t) {
            $totalPiutang += (float) $t->total_owed;
            $piutangCount++;
        }

        $totalOmzet = Transaction::where('status', 'Lunas')
            ->where('is_bonus', false)
            ->get()
            ->sum(fn ($t) => $t->omzet);

        $totalLaba = Transaction::where('status', 'Lunas')
            ->get()
            ->sum(fn ($t) => $t->laba);

        $customersWithBonusCount = 0;
        $customers = Customer::all();
        foreach ($customers as $customer) {
            $stats = BonusCalculator::getStats($customer);
            if ($stats['bonuses_available'] > 0) {
                $customersWithBonusCount++;
            }
        }

        // LM and BR breakdown
        $omzetLM = 0.00;
        $omzetBR = 0.00;
        $labaLM = 0.00;
        $labaBR = 0.00;

        $paidTransactions = Transaction::where('status', 'Lunas')->with('items')->get();
        foreach ($paidTransactions as $t) {
            foreach ($t->items as $item) {
                if ($item->product_type === 'LM') {
                    $omzetLM += (float) $item->line_omzet;
                    $labaLM += (float) $item->line_laba;
                } else {
                    $omzetBR += (float) $item->line_omzet;
                    $labaBR += (float) $item->line_laba;
                }
            }
        }

        // 2. Trend Calculations for Sparklines (Last 7 Days)
        $piutangTrend = [];
        $omzetTrend = [];
        $labaTrend = [];
        $bonusTrend = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            
            // Piutang outstanding up to that day
            $pVal = Transaction::where('status', 'Piutang')
                ->where('tanggal', '<=', $date)
                ->sum('total_owed');
            $piutangTrend[] = (float) $pVal;

            // Omzet paid on that day
            $oVal = Transaction::where('status', 'Lunas')
                ->where('is_bonus', false)
                ->where('tanggal_pelunasan', $date)
                ->sum('omzet');
            $omzetTrend[] = (float) $oVal;

            // Laba paid on that day
            $lVal = Transaction::where('status', 'Lunas')
                ->where('tanggal_pelunasan', $date)
                ->sum('laba');
            $labaTrend[] = (float) $lVal;
            
            // Customers created up to that day
            $cVal = Customer::where('created_at', '<=', now()->subDays($i)->endOfDay())->count();
            $bonusTrend[] = (float) $cVal;
        }

        // Convert trend data to SVG sparkline points
        $piutangPoints = $this->getSparklinePoints($piutangTrend);
        $omzetPoints = $this->getSparklinePoints($omzetTrend);
        $labaPoints = $this->getSparklinePoints($labaTrend);
        $bonusPoints = $this->getSparklinePoints($bonusTrend);

        // 3. Weekly comparison (Current Week vs Previous Week)
        $prevWeekStart = now()->subDays(14)->toDateString();
        $prevWeekEnd = now()->subDays(7)->toDateString();
        
        $currentWeekStart = now()->subDays(7)->toDateString();
        $currentWeekEnd = now()->toDateString();

        $prevPiutang = Transaction::where('status', 'Piutang')
            ->whereBetween('tanggal', [$prevWeekStart, $prevWeekEnd])
            ->sum('total_owed');
        $currPiutang = Transaction::where('status', 'Piutang')
            ->whereBetween('tanggal', [$currentWeekStart, $currentWeekEnd])
            ->sum('total_owed');
        $piutangDiff = $prevPiutang > 0 ? (($currPiutang - $prevPiutang) / $prevPiutang) * 100 : 0;

        $prevOmzet = Transaction::where('status', 'Lunas')
            ->where('is_bonus', false)
            ->whereBetween('tanggal_pelunasan', [$prevWeekStart, $prevWeekEnd])
            ->sum('omzet');
        $currOmzet = Transaction::where('status', 'Lunas')
            ->where('is_bonus', false)
            ->whereBetween('tanggal_pelunasan', [$currentWeekStart, $currentWeekEnd])
            ->sum('omzet');
        $omzetDiff = $prevOmzet > 0 ? (($currOmzet - $prevOmzet) / $prevOmzet) * 100 : 0;

        $prevLaba = Transaction::where('status', 'Lunas')
            ->whereBetween('tanggal_pelunasan', [$prevWeekStart, $prevWeekEnd])
            ->sum('laba');
        $currLaba = Transaction::where('status', 'Lunas')
            ->whereBetween('tanggal_pelunasan', [$currentWeekStart, $currentWeekEnd])
            ->sum('laba');
        $labaDiff = $prevLaba > 0 ? (($currLaba - $prevLaba) / $prevLaba) * 100 : 0;

        // 4. Recent Activities Feed (Synthesized from Models)
        $activities = [];

        // Customers created recently
        $recentCustomers = Customer::orderBy('created_at', 'desc')->take(3)->get();
        foreach ($recentCustomers as $c) {
            $activities[] = [
                'time' => $c->created_at,
                'description' => "Pelanggan baru <strong>{$c->name}</strong> berhasil didaftarkan.",
                'icon' => 'user-plus',
                'color' => 'indigo'
            ];
        }

        // Transactions created recently
        $recentTrans = Transaction::with('customer')->orderBy('created_at', 'desc')->take(3)->get();
        foreach ($recentTrans as $t) {
            $activities[] = [
                'time' => $t->created_at,
                'description' => "Bon baru <strong style='font-family:monospace'>{$t->nomor_bon}</strong> untuk <strong>{$t->customer->name}</strong> diterbitkan.",
                'icon' => 'file-text',
                'color' => 'blue'
            ];
        }

        // Paid transactions recently
        $recentPaid = Transaction::with('customer')
            ->where('status', 'Lunas')
            ->orderBy('updated_at', 'desc')
            ->take(3)
            ->get();
        foreach ($recentPaid as $t) {
            $activities[] = [
                'time' => $t->updated_at,
                'description' => "Pembayaran diterima untuk Bon <strong style='font-family:monospace'>{$t->nomor_bon}</strong> ({$t->customer->name}).",
                'icon' => 'check-circle',
                'color' => 'green'
            ];
        }

        // Sort activities by timestamp descending
        usort($activities, fn($a, $b) => $b['time']->timestamp <=> $a['time']->timestamp);
        $recentActivities = array_slice($activities, 0, 5);

        // 5. Recent Transactions
        $recentTransactions = Transaction::with('customer')
            ->orderBy('tanggal', 'desc')
            ->latest()
            ->take(5)
            ->get();

        // 6. Recent Invoices
        $recentInvoices = Transaction::with('customer')
            ->orderBy('id', 'desc')
            ->take(5)
            ->get();

        return [
            'totalPiutang' => $totalPiutang,
            'piutangCount' => $piutangCount,
            'totalOmzet' => $totalOmzet,
            'totalLaba' => $totalLaba,
            'customersWithBonusCount' => $customersWithBonusCount,
            'omzetLM' => $omzetLM,
            'omzetBR' => $omzetBR,
            'labaLM' => $labaLM,
            'labaBR' => $labaBR,
            'welcomeName' => auth()->user()->name ?? 'Administrator',
            'piutangPoints' => $piutangPoints,
            'omzetPoints' => $omzetPoints,
            'labaPoints' => $labaPoints,
            'bonusPoints' => $bonusPoints,
            'piutangDiff' => $piutangDiff,
            'omzetDiff' => $omzetDiff,
            'labaDiff' => $labaDiff,
            'recentActivities' => $recentActivities,
            'recentTransactions' => $recentTransactions,
            'recentInvoices' => $recentInvoices,
        ];
    }

    /**
     * Map trend values to 0..100 x and 2..28 y coordinates for SVG sparkline path.
     */
    private function getSparklinePoints(array $values): string
    {
        $count = count($values);
        if ($count === 0) {
            return '0,15 100,15';
        }
        
        $min = min($values);
        $max = max($values);
        $range = $max - $min;
        
        $points = [];
        for ($i = 0; $i < $count; $i++) {
            $x = ($i / ($count - 1)) * 100;
            
            if ($range > 0) {
                // scale y between 2 and 28 (higher values get lower y because y starts at 0 at the top)
                $y = 28 - (($values[$i] - $min) / $range) * 26;
            } else {
                $y = 15;
            }
            
            $points[] = round($x) . ',' . round($y);
        }
        
        return implode(' ', $points);
    }
}
