// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

import {Component, inject, NgZone, OnDestroy} from '@angular/core';
import {CommonModule} from '@angular/common';
import {environment} from '../../environments/environment';

interface SaleEvent {
  seq: number;
  at: string;
  product_id: string;
  name: string;
  unit_price: number;
  quantity: number;
  amount: number;
  running_total: number;
  buyer: string;
  city: string;
}

/**
 * Floating "Live shop activity" widget. When opened it subscribes to the API's
 * Server-Sent Events endpoint (/sales-stream) and shows purchases as they arrive.
 * The connection is only open while the widget is, so we never leave a stream
 * running in the background.
 */
@Component({
  selector: 'app-live-activity-widget',
  templateUrl: './live-activity-widget.component.html',
  styleUrls: ['./live-activity-widget.component.css'],
  imports: [CommonModule]
})
export class LiveActivityWidgetComponent implements OnDestroy {
  private readonly zone = inject(NgZone);
  private source?: EventSource;

  isOpen = false;
  connected = false;
  sales: SaleEvent[] = [];
  totalCount = 0;
  totalRevenue = 0;

  private readonly maxItems = 25;

  toggle(): void {
    this.isOpen = !this.isOpen;
    this.isOpen ? this.connect() : this.disconnect();
  }

  close(): void {
    this.isOpen = false;
    this.disconnect();
  }

  clear(): void {
    this.sales = [];
    this.totalCount = 0;
    this.totalRevenue = 0;
  }

  private connect(): void {
    if (this.source) {
      return;
    }
    // Finite bursts; the browser reconnects automatically when the server
    // finishes one, which keeps the feed flowing while the widget is open.
    const url = `${environment.apiUrl}/sales-stream?interval=1000&limit=30`;
    const source = new EventSource(url);
    this.source = source;

    // Native connection-open event: the stream is live.
    source.addEventListener('open', () => this.zone.run(() => this.connected = true));

    source.addEventListener('sale', (event: MessageEvent) => {
      this.zone.run(() => this.onSale(JSON.parse(event.data) as SaleEvent));
    });

    // Browser retries on its own after an error or a normal server close.
    source.onerror = () => this.zone.run(() => this.connected = false);
  }

  private onSale(sale: SaleEvent): void {
    this.sales = [sale, ...this.sales].slice(0, this.maxItems);
    this.totalCount++;
    this.totalRevenue = Math.round((this.totalRevenue + sale.amount) * 100) / 100;
  }

  private disconnect(): void {
    this.source?.close();
    this.source = undefined;
    this.connected = false;
  }

  ngOnDestroy(): void {
    this.disconnect();
  }
}
