export class Pagination<PaginationObject> {
  constructor(
    public readonly data: PaginationObject[],
    public readonly current_page: number,
    public readonly per_page: number,
    public readonly from: number,
    public readonly to: number,
    public readonly total: number,
    public readonly last_page: number
  ) {
  }
}
