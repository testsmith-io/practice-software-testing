export interface Pagination<T> {
  readonly data: T[];
  readonly current_page: number;
  readonly per_page: number;
  readonly from: number;
  readonly to: number;
  readonly total: number;
  readonly last_page: number;
}
