import {Component, EventEmitter, Input, Output} from '@angular/core';

@Component({
  selector: 'app-pagination',
  standalone: true,
  imports: [],
  templateUrl: './pagination.component.html',
  styleUrls: []
})
export class PaginationComponent {
  @Input() currentPage: number;
  @Input() lastPage: number;
  @Output() pageChange = new EventEmitter<number>();

  pageNumbers(): number[] {
    const pagesArray: number[] = [];
    for (let i = 1; i <= this.lastPage; i++) {
      pagesArray.push(i);
    }
    return pagesArray;
  }

  changePage(page: number) {
    if (page >= 1 && page <= this.lastPage && page !== this.currentPage) {
      this.pageChange.emit(page);
    }
  }
}
