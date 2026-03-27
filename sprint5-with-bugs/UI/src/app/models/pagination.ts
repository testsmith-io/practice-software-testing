// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

export interface Pagination<T> {
  readonly data: T[];
  readonly current_page: number;
  readonly per_page: number;
  readonly from: number;
  readonly to: number;
  readonly total: number;
  readonly last_page: number;
}
