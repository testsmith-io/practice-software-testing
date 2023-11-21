export class Pagination<PaginationObject> {
  constructor(
    public readonly data: PaginationObject[],
    public readonly per_page: number,
    public readonly total: number,
    public readonly last_page: number
  ) {
  }
}
