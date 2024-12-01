<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class ReportService
{
    public function getTotalSalesPerCountry()
    {
        return DB::table('invoices')
            ->selectRaw('SUM(total) as "total_sales", billing_country')
            ->where('status', '=', 'COMPLETED')
            ->groupBy('billing_country')
            ->get();
    }

    public function getTop10PurchasedProducts()
    {
        return DB::table('products AS p')
            ->join('invoice_items AS i', 'i.product_id', '=', 'p.id')
            ->selectRaw('p.name, count(p.name) as count')
            ->groupBy('p.name')
            ->orderByDesc('count')
            ->limit(10)
            ->get();
    }

    public function getTop10BestSellingCategories()
    {
        return DB::table('categories AS c')
            ->join('products AS p', 'p.category_id', '=', 'c.id')
            ->join('invoice_items AS i', 'i.product_id', '=', 'p.id')
            ->selectRaw('c.name as category_name, SUM(i.unit_price) as total_earned')
            ->groupBy('c.name')
            ->orderByDesc('total_earned')
            ->limit(10)
            ->get();
    }

    public function getTotalSalesOfYears($startYear, $endYear, $driver)
    {
        if ($driver == 'sqlite') {
            $yearQuery = "strftime('%Y', invoice_date) AS year";
            $yearGroup = "strftime('%Y', invoice_date)";
        } elseif ($driver == 'mysql') {
            $yearQuery = 'YEAR(invoice_date) AS year';
            $yearGroup = 'YEAR(invoice_date)';
        }

        return DB::table('invoices')
            ->selectRaw("SUM(total) AS total, $yearQuery")
            ->whereYear('invoice_date', '>=', $startYear)
            ->where('status', '=', 'COMPLETED')
            ->groupBy(DB::raw($yearGroup))
            ->get();
    }

    public function getAverageSalesPerMonth($year, $driver)
    {
        if ($driver == 'sqlite') {
            $monthQuery = 'CAST(strftime("%m", invoice_date) AS INTEGER) AS month';
            $monthGroup = 'strftime("%m", invoice_date)';
        } elseif ($driver == 'mysql') {
            $monthQuery = 'MONTH(invoice_date) AS month';
            $monthGroup = 'MONTH(invoice_date)';
        }

        return DB::table('invoices')
            ->selectRaw("$monthQuery, AVG(total) AS average, COUNT(*) AS amount")
            ->whereYear('invoice_date', '=', $year)
            ->where('status', '=', 'COMPLETED')
            ->groupBy(DB::raw($monthGroup))
            ->get();
    }

    public function getAverageSalesPerWeek($year, $driver)
    {
        if ($driver == 'sqlite') {
            $weekQuery = 'CAST(strftime("%W", invoice_date) AS INTEGER) AS week';
            $weekGroup = 'strftime("%W", invoice_date)';
        } elseif ($driver == 'mysql') {
            $weekQuery = 'WEEK(invoice_date) AS week';
            $weekGroup = 'WEEK(invoice_date)';
        }

        return DB::table('invoices')
            ->selectRaw("$weekQuery, AVG(total) AS average, COUNT(*) AS amount")
            ->whereYear('invoice_date', '=', $year)
            ->where('status', '=', 'COMPLETED')
            ->groupBy(DB::raw($weekGroup))
            ->get();
    }

    public function getCustomersByCountry()
    {
        return DB::table('users AS u')
            ->selectRaw('COUNT(*) AS amount, u.country')
            ->where('u.role', '=', 'user')
            ->groupBy('u.country')
            ->get();
    }
}
