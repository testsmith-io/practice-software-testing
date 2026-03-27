import {ChangeDetectorRef, Component, inject, OnDestroy, OnInit} from '@angular/core';
import {CartService} from "../_services/cart.service";
import {CustomerAccountService} from "../shared/customer-account.service";
import {Subscription} from "rxjs";
import {RouterLink} from "@angular/router";

@Component({
  selector: 'app-header',
  templateUrl: './header.component.html',
  imports: [
    RouterLink
  ],
  styleUrls: ['./header.component.css']
})
export class HeaderComponent implements OnDestroy, OnInit {
  private readonly auth = inject(CustomerAccountService);
  private readonly cartService = inject(CartService);
  private readonly changeDetectorRef = inject(ChangeDetectorRef);

  items: any;
  role: string = '';
  name: string = '';
  isLoggedIn: boolean;
  subscription: Subscription;

  constructor() {
    this.cartService.storageSub.subscribe(() => {
      this.items = this.getCartItems();
      this.changeDetectorRef.detectChanges();
    })
    this.subscription = this.auth.authSub.subscribe(loggedIn => {
      if (loggedIn) {
        this.isLoggedIn = true;
        this.getSignedInUser();
      } else {
        this.name = '';
        this.role = '';
      }
    });
  }

  ngOnInit(): void {
    this.items = this.getCartItems();
    this.role = this.auth.getRole();
    this.getSignedInUser();

    // Check if we should auto-open bug hunting guide based on query parameter
    this.checkAndRestoreSplitScreen();
  }

  ngOnDestroy() {
    this.subscription.unsubscribe();
  }

  getCartItems() {
    let items = this.cartService.getItems();
    if (items != null && items.length) {
      return items.map((item: { is_rental: number, quantity: number }) => (item.is_rental === 1) ? 1 : item.quantity).reduce((acc: any, item: any) => item + acc);
    }
  }

  getSignedInUser() {
    this.auth.getDetails().subscribe(res => {
      this.role = this.auth.getRole();
      this.name = res.first_name + ' ' + res.last_name;
    })
  }

  logout() {
    this.auth.logout();
    window.location.reload();
  }

  private checkAndRestoreSplitScreen(): void {
    // Check if we should activate bug hunting split-screen from query parameter or localStorage

    // For Angular hash routing, query params are in the hash portion: #/?bug-hunting=true
    let bugHinting: string | null = null;

    // Try getting from hash (Angular routing)
    const hash = window.location.hash;
    if (hash && hash.includes('?')) {
      const queryString = hash.split('?')[1];
      const urlParams = new URLSearchParams(queryString);
      bugHinting = urlParams.get('bug-hunting');
    }

    // Fallback to standard query string (for non-hash routing)
    if (!bugHinting && window.location.search) {
      const urlParams = new URLSearchParams(window.location.search);
      bugHinting = urlParams.get('bug-hunting');
    }

    const shouldActivateBugHunting = localStorage.getItem('bug-hunting-split-screen-active');

    // Only activate if explicitly set to 'true', not if set to 'false'
    if ((bugHinting === 'true' || shouldActivateBugHunting === 'true') && bugHinting !== 'false') {
      // Store in localStorage for persistence across reloads
      localStorage.setItem('bug-hunting-split-screen-active', 'true');

      // Check if we're NOT already in an iframe (to prevent recursive split screens)
      if (window === window.parent) {
        // Small delay to ensure full initialization
        setTimeout(() => {
          // Check one more time that split screen doesn't already exist
          if (!document.getElementById('bug-hunting-split-screen')) {
            this.createBugHuntingSplitScreenMode();
          }
        }, 500);
      }
    }
  }

  openBugHuntingGuide(): void {
    // Check if we're inside an iframe (already in split-screen)
    if (window !== window.parent) {
      // We're already in split-screen mode, don't create another
      return;
    }

    // Check if on mobile device
    if (this.isMobileDevice()) {
      this.showMobileWarning('Bug Hunting Guide');
      return;
    }

    // Set query parameter to true in the URL
    const hash = window.location.hash;
    let cleanPath = '#/';

    if (hash && hash.includes('?')) {
      cleanPath = hash.split('?')[0];
    } else if (hash && hash !== '') {
      cleanPath = hash;
    }

    // Update hash with bug-hunting=true
    window.location.hash = cleanPath + '?bug-hunting=true';

    // Reload to trigger the split screen
    window.location.reload();
  }

  private isMobileDevice(): boolean {
    const userAgent = navigator.userAgent || navigator.vendor || (window as any).opera;

    // Check for mobile user agents
    const mobileRegex = /android|webos|iphone|ipad|ipod|blackberry|iemobile|opera mini/i;
    if (mobileRegex.test(userAgent.toLowerCase())) {
      return true;
    }

    // Check screen size (consider tablets as mobile for this use case)
    return window.innerWidth <= 768 || window.innerHeight <= 600;
  }

  private showMobileWarning(guideType: string = 'Bug Hunting Guide'): void {
    // Create mobile warning modal
    const modal = document.createElement('div');
    modal.style.cssText = `
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0,0,0,0.8);
      z-index: 10000;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
    `;

    const content = document.createElement('div');
    content.style.cssText = `
      background: white;
      padding: 30px;
      border-radius: 15px;
      max-width: 400px;
      text-align: center;
      box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    `;

    content.innerHTML = `
      <div style="font-size: 48px; margin-bottom: 20px;">💻</div>
      <h2 style="color: #dc3545; margin-bottom: 20px; font-size: 24px;">Desktop Required</h2>
      <p style="margin-bottom: 20px; line-height: 1.6; color: #555;">
        The ${guideType} requires a desktop or laptop computer for the best experience.
      </p>
      <p style="margin-bottom: 25px; font-size: 14px; color: #666;">
        The split-screen interface needs adequate screen space to display both the guide and the application side-by-side.
      </p>
      <button id="closeMobileModal" style="
        background: #dc3545;
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 8px;
        cursor: pointer;
        font-size: 16px;
        font-weight: 600;
        transition: background 0.2s;
      " onmouseover="this.style.background='#c82333'" onmouseout="this.style.background='#dc3545'">
        Got it
      </button>
    `;

    modal.appendChild(content);
    document.body.appendChild(modal);

    // Close modal functionality
    const closeModal = () => {
      document.body.removeChild(modal);
    };

    content.querySelector('#closeMobileModal')?.addEventListener('click', closeModal);
    modal.addEventListener('click', (e) => {
      if (e.target === modal) {
        closeModal();
      }
    });

    // Close on escape key
    const handleEscape = (e: KeyboardEvent) => {
      if (e.key === 'Escape') {
        closeModal();
        document.removeEventListener('keydown', handleEscape);
      }
    };
    document.addEventListener('keydown', handleEscape);
  }

  private createBugHuntingSplitScreenMode(): void {
    // Check if split screen already exists
    if (document.getElementById('bug-hunting-split-screen')) {
      this.closeBugHuntingSplitScreenMode();
      return;
    }

    // Save split-screen state to localStorage
    localStorage.setItem('bug-hunting-split-screen-active', 'true');

    // Create the split screen container
    const splitContainer = document.createElement('div');
    splitContainer.id = 'bug-hunting-split-screen';
    splitContainer.style.cssText = `
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100vh;
      z-index: 9999;
      display: flex;
      background: #000;
    `;

    // Create the guide panel (25% width, left side) - red theme
    const guidePanel = document.createElement('div');
    guidePanel.id = 'bug-guide-panel';
    guidePanel.style.cssText = `
      width: 25%;
      height: 100%;
      background: white;
      border-right: 2px solid #dc3545;
      overflow-y: auto;
      overflow-x: hidden;
      position: relative;
      contain: layout style;
    `;

    // Create guide content container
    const guideContent = document.createElement('div');
    guideContent.id = 'bug-guide-content';
    guideContent.style.cssText = `
      padding: 20px;
      max-width: 100%;
      box-sizing: border-box;
    `;

    // Store reference to the script element for cleanup
    const bugScriptId = 'bug-hunting-script-' + Date.now();

    // Load bug hunting guide content via fetch
    fetch('/assets/bug-hunting-guide.html')
      .then(response => response.text())
      .then(html => {
        // Parse and inject the HTML content
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');

        // Extract body content
        const bodyContent = doc.body.innerHTML;

        // Extract styles
        const styles = doc.querySelectorAll('style');
        let styleContent = '';
        styles.forEach(style => {
          styleContent += style.textContent;
        });

        // Extract scripts
        const scripts = doc.querySelectorAll('script');
        let scriptContent = '';
        scripts.forEach(script => {
          scriptContent += script.textContent;
        });

        // Create scoped style element
        const scopedStyle = document.createElement('style');
        scopedStyle.textContent = `
          #bug-guide-content {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            line-height: 1.6;
          }
          #bug-guide-content .main-container {
            max-width: 100% !important;
            padding: 10px !important;
          }
          #bug-guide-content .bug-card {
            margin-bottom: 10px !important;
            padding: 15px !important;
          }
          #bug-guide-content .nav-button {
            padding: 8px 12px !important;
            font-size: 12px !important;
          }
          #bug-guide-content h1 { font-size: 1.5em; }
          #bug-guide-content h2 { font-size: 1.3em; }
          #bug-guide-content h3 { font-size: 1.1em; }
          ${styleContent}
        `;

        guidePanel.appendChild(scopedStyle);

        // Remove script tags from body content
        const bodyWithoutScripts = bodyContent.replace(/<script[^>]*>[\s\S]*?<\/script>/gi, '');
        guideContent.innerHTML = bodyWithoutScripts;

        // Execute scripts in global context
        const scriptElement = document.createElement('script');
        scriptElement.id = bugScriptId;
        scriptElement.textContent = scriptContent;
        document.head.appendChild(scriptElement);

        console.log('Bug hunting guide content loaded successfully');

        // Initialize the bug hunting guide if function exists
        if ((window as any).loadProgress) {
          (window as any).loadProgress();
        }
      })
      .catch(error => {
        console.error('Failed to load bug hunting guide content:', error);
        guideContent.innerHTML = '<p style="color: red; padding: 20px;">Failed to load bug hunting guide content.</p>';
      });

    // Create the application panel (75% width, right side)
    const appPanel = document.createElement('div');
    appPanel.id = 'bug-app-panel';
    appPanel.style.cssText = `
      width: 75%;
      height: 100%;
      background: white;
      position: relative;
      overflow: auto;
      contain: layout style;
    `;

    // Store references to original body children
    const originalBodyChildren = Array.from(document.body.children);
    const originalStyles = document.body.style.cssText;
    const originalBodyClasses = document.body.className;

    // Create app content container
    const appContent = document.createElement('div');
    appContent.id = 'bug-app-content';
    appContent.style.cssText = `
      width: 100%;
      height: 100%;
      position: relative;
      overflow: auto;
    `;

    // Move the existing Angular app DOM nodes
    originalBodyChildren.forEach(child => {
      appContent.appendChild(child);
    });

    // Apply styles
    const appStyles = document.createElement('style');
    appStyles.textContent = `
      #bug-app-content {
        min-height: 100%;
      }
      #bug-app-content .testing-notification-bar {
        display: none !important;
      }
      #bug-app-content .row > .col > .container {
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)) !important;
      }
    `;
    appPanel.appendChild(appStyles);

    // Create close button - red theme
    const closeButton = document.createElement('button');
    closeButton.innerHTML = '✕ Close Bug Hunting';
    closeButton.style.cssText = `
      position: fixed;
      top: 10px;
      right: 10px;
      z-index: 10001;
      background: #dc3545;
      color: white;
      border: none;
      padding: 10px 16px;
      border-radius: 6px;
      cursor: pointer;
      font-size: 14px;
      font-weight: 600;
      box-shadow: 0 2px 8px rgba(220, 53, 69, 0.3);
      transition: all 0.2s ease;
      user-select: none;
    `;

    closeButton.addEventListener('mouseenter', () => {
      closeButton.style.background = '#c82333';
      closeButton.style.transform = 'translateY(-1px)';
      closeButton.style.boxShadow = '0 4px 12px rgba(220, 53, 69, 0.4)';
    });

    closeButton.addEventListener('mouseleave', () => {
      closeButton.style.background = '#dc3545';
      closeButton.style.transform = 'translateY(0)';
      closeButton.style.boxShadow = '0 2px 8px rgba(220, 53, 69, 0.3)';
    });

    closeButton.addEventListener('mousedown', () => {
      closeButton.style.transform = 'translateY(0)';
    });

    // Close handler - sets bug-hunting=false to prevent reopening
    const closeHandler = () => {
      localStorage.removeItem('bug-hunting-split-screen-active');

      // Set query parameter to false in the hash
      const hash = window.location.hash;
      let cleanPath = '#/';

      if (hash && hash.includes('?')) {
        cleanPath = hash.split('?')[0];
      } else if (hash && hash !== '') {
        cleanPath = hash;
      }

      // Update hash with bug-hunting=false
      window.location.hash = cleanPath + '?bug-hunting=false';

      // Reload the window
      window.location.reload();
    };

    closeButton.addEventListener('click', closeHandler);
    closeButton.onclick = closeHandler;

    // Create resize handle - red theme
    const resizeHandle = document.createElement('div');
    resizeHandle.style.cssText = `
      position: fixed;
      top: 0;
      left: calc(25% - 5px);
      width: 10px;
      height: 100vh;
      background: #dc3545;
      cursor: ew-resize;
      z-index: 10002;
      transition: background 0.2s;
      border-radius: 0 5px 5px 0;
      opacity: 0.8;
    `;

    resizeHandle.innerHTML = '<div style="width: 2px; height: 20px; background: white; margin: 50% auto 0; border-radius: 1px;"></div>';

    // Add hover effect
    let isResizing = false;
    resizeHandle.addEventListener('mouseenter', () => {
      if (!isResizing) {
        resizeHandle.style.background = '#c82333';
        resizeHandle.style.opacity = '1';
      }
    });

    resizeHandle.addEventListener('mouseleave', () => {
      if (!isResizing) {
        resizeHandle.style.background = '#dc3545';
        resizeHandle.style.opacity = '0.8';
      }
    });

    // Add resize functionality
    let startX = 0;
    let startWidth = 0;

    const handleMouseDown = (e: MouseEvent) => {
      isResizing = true;
      startX = e.clientX;
      startWidth = parseInt(window.getComputedStyle(guidePanel).width, 10);

      resizeHandle.style.background = '#c82333';
      resizeHandle.style.opacity = '1';
      document.body.style.cursor = 'ew-resize';
      document.body.style.userSelect = 'none';

      e.preventDefault();
      e.stopPropagation();

      document.addEventListener('mousemove', handleMouseMove, { passive: false, capture: true });
      document.addEventListener('mouseup', handleMouseUp, { passive: false, capture: true });
      window.addEventListener('mousemove', handleMouseMove, { passive: false });
      window.addEventListener('mouseup', handleMouseUp, { passive: false });
    };

    const handleMouseMove = (e: MouseEvent) => {
      if (!isResizing) return;

      e.preventDefault();
      e.stopPropagation();

      if (typeof e.clientX !== 'number') return;

      const deltaX = e.clientX - startX;
      const newWidth = startWidth + deltaX;
      const containerWidth = splitContainer.offsetWidth;

      if (containerWidth <= 0) return;

      const percentage = Math.min(Math.max((newWidth / containerWidth) * 100, 15), 60);

      requestAnimationFrame(() => {
        if (guidePanel && appPanel) {
          guidePanel.style.width = percentage + '%';
          appPanel.style.width = (100 - percentage) + '%';
          resizeHandle.style.left = `calc(${percentage}% - 5px)`;
        }
      });
    };

    const handleMouseUp = (e: MouseEvent) => {
      if (!isResizing) return;

      isResizing = false;

      resizeHandle.style.background = '#dc3545';
      resizeHandle.style.opacity = '0.8';
      document.body.style.cursor = '';
      document.body.style.userSelect = '';

      document.removeEventListener('mousemove', handleMouseMove, { capture: true } as any);
      document.removeEventListener('mouseup', handleMouseUp, { capture: true } as any);
      window.removeEventListener('mousemove', handleMouseMove);
      window.removeEventListener('mouseup', handleMouseUp);

      e.preventDefault();
      e.stopPropagation();
    };

    resizeHandle.addEventListener('mousedown', handleMouseDown);

    // Assemble the split screen
    guidePanel.appendChild(guideContent);
    appPanel.appendChild(appContent);
    splitContainer.appendChild(guidePanel);
    splitContainer.appendChild(appPanel);

    // Store original state
    (window as any).bugHuntingSplitScreen = {
      originalBodyChildren,
      originalStyles,
      originalBodyClasses,
      container: splitContainer,
      scriptId: bugScriptId
    };

    // Show split screen
    document.body.style.cssText = 'margin: 0; padding: 0; overflow: hidden;';
    document.body.appendChild(splitContainer);
    document.body.appendChild(resizeHandle);
    document.body.appendChild(closeButton);

    // Handle escape key to close
    const handleEscape = (e: KeyboardEvent) => {
      if (e.key === 'Escape') {
        closeHandler();
      }
    };
    document.addEventListener('keydown', handleEscape);
    (window as any).bugHuntingSplitScreen.escapeHandler = handleEscape;

    // Ensure Angular app maintains functionality
    this.reinitializeAngularInPanel(appContent);
  }

  private reinitializeAngularInPanel(container: HTMLElement): void {
    // Hide the testing notification bar that would duplicate
    const testingBars = container.querySelectorAll('.testing-notification-bar');
    testingBars.forEach(bar => {
      (bar as HTMLElement).style.display = 'none';
    });

    // Ensure proper scrolling behavior
    container.style.overflowY = 'auto';
    container.style.height = '100%';
  }

  private closeBugHuntingSplitScreenMode(): void {
    // Remove localStorage
    localStorage.removeItem('bug-hunting-split-screen-active');

    // Set query parameter to 'false' to prevent reopening
    const baseUrl = window.location.origin + window.location.pathname;

    // Extract the path without query params
    const hash = window.location.hash;
    let cleanPath = '#/';

    if (hash && hash.includes('?')) {
      // Get just the path part before the ?
      cleanPath = hash.split('?')[0];
    } else if (hash && hash !== '') {
      cleanPath = hash;
    }

    // Navigate with bug-hunting=false to prevent reopening
    window.location.href = baseUrl + cleanPath + '?bug-hunting=false';
  }

}
