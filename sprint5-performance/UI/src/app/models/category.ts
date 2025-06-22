export class Category {
  id!: number;
  parent_id!: number;
  name!: string;
  slug!: string;
  sub_categories!: Category[];
}
