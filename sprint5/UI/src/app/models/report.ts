// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

export interface SalesPerYear {
  year: string;
  total: number;
}

export interface AverageSalesPerMonth {
  month: string;
  average: number;
  amount: number;
}

export interface AverageSalesPerWeek {
  week: string;
  average: number;
  amount: number;
}

export interface SalesPerCountry {
  billing_country: string;
  total_sales: number;
}

export interface TopPurchasedProduct {
  name: string;
  count: number;
}

export interface TopSellingCategory {
  category_name: string;
  total_earned: number;
}

export interface CustomerPerCountry {
  country: string;
  amount: number;
}
