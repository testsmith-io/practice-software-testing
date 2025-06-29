import {AfterViewInit, Directive, ElementRef, Input, OnDestroy, Renderer2} from '@angular/core';

@Directive({
  selector: '[appRenderDelay]'
})
export class RenderDelayDirective implements AfterViewInit, OnDestroy {
  @Input() delayMs?: number;
  @Input() delayRandomRange?: [number, number];

  private unlisteners: (() => void)[] = [];

  constructor(private el: ElementRef, private renderer: Renderer2) {}

  ngAfterViewInit(): void {
    const native = this.el.nativeElement;
    native.style.opacity = '0.3';

    const delay = this.delayMs ?? this.getRandomDelay();

    const buttons = native.querySelectorAll('button');
    const anchors = native.querySelectorAll('a');

    // Disable buttons
    buttons.forEach((btn: HTMLElement) => {
      this.renderer.setAttribute(btn, 'disabled', 'true');
    });

    // Disable <a> elements
    anchors.forEach((anchor: HTMLElement) => {
      this.renderer.setStyle(anchor, 'pointerEvents', 'none');
      this.renderer.setStyle(anchor, 'opacity', '0.5');
      this.renderer.setStyle(anchor, 'cursor', 'not-allowed');

      const blockClick = this.renderer.listen(anchor, 'click', (e) => {
        e.preventDefault();
        e.stopImmediatePropagation();
        return false;
      });

      this.unlisteners.push(blockClick);
    });

    // Re-enable after delay
    setTimeout(() => {
      native.style.opacity = '1';

      buttons.forEach((btn: HTMLElement) =>
        this.renderer.removeAttribute(btn, 'disabled')
      );

      anchors.forEach((anchor: HTMLElement) => {
        this.renderer.removeStyle(anchor, 'pointerEvents');
        this.renderer.removeStyle(anchor, 'opacity');
        this.renderer.removeStyle(anchor, 'cursor');
      });

      this.unlisteners.forEach(unlisten => unlisten());
    }, delay);
  }

  ngOnDestroy(): void {
    this.unlisteners.forEach(unlisten => unlisten());
  }

  private getRandomDelay(): number {
    const [min, max] = this.delayRandomRange ?? [500, 2000];
    return Math.floor(Math.random() * (max - min + 1)) + min;
  }
}
