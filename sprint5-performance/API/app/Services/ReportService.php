<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReportService
{
    public function getTotalSalesPerCountry()
    {
        Log::info('Fetching total sales per country');

        $result = DB::table('invoices')
            ->selectRaw('SUM(total) as "total_sales", billing_country')
            ->where('status', '=', 'COMPLETED')
            ->groupBy('billing_country')
            ->get();

        Log::debug('Total sales per country result', ['rows' => $result->count()]);
        return $result;
    }

    public function getTop10PurchasedProducts()
    {
        Log::info('Fetching top 10 purchased products');

        $result = DB::table('products AS p')
            ->join('invoice_items AS i', 'i.product_id', '=', 'p.id')
            ->selectRaw('p.name, count(p.name) as count')
            ->groupBy('p.name')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        Log::debug('Top 10 products result', ['rows' => $result->count()]);
        return $result;
    }

    public function getTop10BestSellingCategories()
    {
        Log::info('Fetching top 10 best selling categories');

        $result = DB::table('categories AS c')
            ->join('products AS p', 'p.category_id', '=', 'c.id')
            ->join('invoice_items AS i', 'i.product_id', '=', 'p.id')
            ->selectRaw('c.name as category_name, SUM(i.unit_price) as total_earned')
            ->groupBy('c.name')
            ->orderByDesc('total_earned')
            ->limit(10)
            ->get();

        Log::debug('Top 10 categories result', ['rows' => $result->count()]);
        return $result;
    }

    public function getTotalSalesOfYears($startYear, $endYear, $driver)
    {
        Log::info('Fetching total sales by year', [
            'startYear' => $startYear,
            'endYear' => $endYear,
            'driver' => $driver
        ]);

        if ($driver == 'sqlite') {
            $yearQuery = "strftime('%Y', invoice_date) AS year";
            $yearGroup = "strftime('%Y', invoice_date)";
        } elseif ($driver == 'mysql') {
            $yearQuery = 'YEAR(invoice_date) AS year';
            $yearGroup = 'YEAR(invoice_date)';
        }

        $result = DB::table('invoices')
            ->selectRaw("SUM(total) AS total, $yearQuery")
            ->whereYear('invoice_date', '>=', $startYear)
            ->where('status', '=', 'COMPLETED')
            ->groupBy(DB::raw($yearGroup))
            ->get();

        Log::debug('Total sales per year result', ['rows' => $result->count()]);
        return $result;
    }

    public function getAverageSalesPerMonth($year, $driver)
    {
        Log::info('Fetching average sales per month', [
            'year' => $year,
            'driver' => $driver
        ]);

        if ($driver == 'sqlite') {
            $monthQuery = 'CAST(strftime("%m", invoice_date) AS INTEGER) AS month';
            $monthGroup = 'strftime("%m", invoice_date)';
        } elseif ($driver == 'mysql') {
            $monthQuery = 'MONTH(invoice_date) AS month';
            $monthGroup = 'MONTH(invoice_date)';
        }

        $result = DB::table('invoices')
            ->selectRaw("$monthQuery, AVG(total) AS average, COUNT(*) AS amount")
            ->whereYear('invoice_date', '=', $year)
            ->where('status', '=', 'COMPLETED')
            ->groupBy(DB::raw($monthGroup))
            ->get();

        Log::debug('Average sales per month result', ['rows' => $result->count()]);
        return $result;
    }

    public function getAverageSalesPerWeek($year, $driver)
    {
        Log::info('Fetching average sales per week', [
            'year' => $year,
            'driver' => $driver
        ]);

        if ($driver == 'sqlite') {
            $weekQuery = 'CAST(strftime("%W", invoice_date) AS INTEGER) AS week';
            $weekGroup = 'strftime("%W", invoice_date)';
        } elseif ($driver == 'mysql') {
            $weekQuery = 'WEEK(invoice_date) AS week';
            $weekGroup = 'WEEK(invoice_date)';
        }

        $result = DB::table('invoices')
            ->selectRaw("$weekQuery, AVG(total) AS average, COUNT(*) AS amount")
            ->whereYear('invoice_date', '=', $year)
            ->where('status', '=', 'COMPLETED')
            ->groupBy(DB::raw($weekGroup))
            ->get();

        Log::debug('Average sales per week result', ['rows' => $result->count()]);
        return $result;
    }

    public function getCustomersByCountry()
    {
        Log::info('Fetching customer count per country');

        $result = DB::table('users AS u')
            ->selectRaw('COUNT(*) AS amount, u.country')
            ->where('u.role', '=', 'user')
            ->groupBy('u.country')
            ->get();

        Log::debug('Customers by country result', ['rows' => $result->count()]);
        return $result;
    }
}
