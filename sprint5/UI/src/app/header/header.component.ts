import {ChangeDetectorRef, Component, inject, OnDestroy, OnInit} from '@angular/core';
import {CartService} from "../_services/cart.service";
import {CustomerAccountService} from "../shared/customer-account.service";
import {Subscription} from "rxjs";
import {TranslocoDirective, TranslocoService} from "@jsverse/transloco";
import {RouterLink} from "@angular/router";
import {FaIconComponent} from "@fortawesome/angular-fontawesome";
import {UpperCasePipe} from "@angular/common";

@Component({
  selector: 'app-header',
  templateUrl: './header.component.html',
  imports: [
    RouterLink,
    FaIconComponent,
    UpperCasePipe,
    TranslocoDirective
  ],
  styleUrls: ['./header.component.css']
})
export class HeaderComponent implements OnDestroy, OnInit {
  private auth = inject(CustomerAccountService);
  private cartService = inject(CartService);
  private changeDetectorRef = inject(ChangeDetectorRef);
  private translocoService = inject(TranslocoService);

  activeLanguage: string;
  items: any;
  role: string = '';
  name: string = '';
  isLoggedIn: boolean;
  subscription: Subscription;
  showBugHuntingButton: boolean = false;

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
    this.activeLanguage = this.translocoService.getActiveLang();
    this.getSignedInUser();

    // Check if Bug Hunting button should be shown (only on deployed version)
    this.showBugHuntingButton = this.isDeployedVersion();

    // Check if we're coming from a split-screen reload and need to activate it
    this.checkAndRestoreSplitScreen();
  }

  ngOnDestroy() {
    this.subscription.unsubscribe();
  }

  getCartItems() {
    return this.cartService.getQuantity();
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

  changeSiteLanguage(language: string): void {
    this.translocoService.setActiveLang(language);
    localStorage.setItem('language', language);
    this.activeLanguage = language;
  }

  private checkAndRestoreSplitScreen(): void {
    // Check if we should activate testing split-screen
    const shouldActivateTesting = localStorage.getItem('testing-split-screen-active');
    // Check if we should activate bug hunting split-screen
    const shouldActivateBugHunting = localStorage.getItem('bug-hunting-split-screen-active');

    if (shouldActivateTesting === 'true') {
      // Check if we're NOT already in an iframe (to prevent recursive split screens)
      if (window === window.parent) {
        // Small delay to ensure full initialization
        setTimeout(() => {
          // Check one more time that split screen doesn't already exist
          if (!document.getElementById('testing-split-screen')) {
            this.createSplitScreenMode();
          }
        }, 500);
      }
    } else if (shouldActivateBugHunting === 'true') {
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

  openTestingGuide(): void {
    // Check if we're inside an iframe (already in split-screen)
    if (window !== window.parent) {
      // We're already in split-screen mode, don't create another
      return;
    }

    // Check if on mobile device
    if (this.isMobileDevice()) {
      this.showMobileWarning();
      return;
    }
    this.createSplitScreenMode();
  }

  openBugHuntingGuide(): void {
    // Open the with-bugs site with query parameter to control sidepanel
    // Use hash routing format for Angular: #/?bug-hunting=true
    const bugHuntingUrl = 'https://with-bugs.practicesoftwaretesting.com/#/?bug-hunting=true';
    window.open(bugHuntingUrl, '_blank');
  }

  private isDeployedVersion(): boolean {
    // Check if running on deployed version vs Docker/localhost
    const hostname = window.location.hostname;
    const isLocal = hostname === 'localhost' || hostname === '127.0.0.1' || hostname === '0.0.0.0';
    const isDeployed = hostname.includes('practicesoftwaretesting.com');

    // Show button only on deployed version, not on Docker/local
    return isDeployed && !isLocal;
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

  private showMobileWarning(guideType: string = 'Black Box Testing Guide'): void {
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
      <h2 style="color: #007bff; margin-bottom: 20px; font-size: 24px;">Desktop Required</h2>
      <p style="margin-bottom: 20px; line-height: 1.6; color: #555;">
        The Black Box Testing Guide requires a desktop or laptop computer for the best experience.
      </p>
      <p style="margin-bottom: 25px; font-size: 14px; color: #666;">
        The split-screen interface needs adequate screen space to display both the testing guide and the application side-by-side.
      </p>
      <button id="closeMobileModal" style="
        background: #007bff;
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 8px;
        cursor: pointer;
        font-size: 16px;
        font-weight: 600;
        transition: background 0.2s;
      " onmouseover="this.style.background='#0056b3'" onmouseout="this.style.background='#007bff'">
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

  private createSplitScreenMode(): void {
    // Check if split screen already exists
    if (document.getElementById('testing-split-screen')) {
      this.closeSplitScreenMode();
      return;
    }

    // Save split-screen state to localStorage
    localStorage.setItem('testing-split-screen-active', 'true');

    // Create the split screen container
    const splitContainer = document.createElement('div');
    splitContainer.id = 'testing-split-screen';
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

    // Create the guide panel (25% width, left side)
    const guidePanel = document.createElement('div');
    guidePanel.id = 'guide-panel';
    guidePanel.style.cssText = `
      width: 25%;
      height: 100%;
      background: white;
      border-right: 2px solid #007bff;
      overflow-y: auto;
      overflow-x: hidden;
      position: relative;
      contain: layout style;
    `;

    // Create guide content container
    const guideContent = document.createElement('div');
    guideContent.id = 'guide-content';
    guideContent.style.cssText = `
      padding: 20px;
      max-width: 100%;
      box-sizing: border-box;
    `;

    // Store reference to the script element for cleanup (moved up here)
    const guideScriptId = 'testing-guide-script-' + Date.now();

    // Load guide content via fetch
    fetch('/assets/testing-guide.html')
      .then(response => response.text())
      .then(html => {
        // Parse and inject the HTML content
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');

        // Extract body content
        const bodyContent = doc.body.innerHTML;

        // Extract styles if any
        const styles = doc.querySelectorAll('style');
        let styleContent = '';
        styles.forEach(style => {
          styleContent += style.textContent;
        });

        // Extract scripts and make them available globally
        const scripts = doc.querySelectorAll('script');
        let scriptContent = '';
        scripts.forEach(script => {
          scriptContent += script.textContent;
        });

        // Create scoped style element
        const scopedStyle = document.createElement('style');
        scopedStyle.textContent = `
          #guide-content {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            line-height: 1.6;
          }
          #guide-content .main-container {
            max-width: 100% !important;
            padding: 10px !important;
          }
          #guide-content .exercise-card {
            margin-bottom: 10px !important;
            padding: 15px !important;
          }
          #guide-content .features-grid {
            grid-template-columns: 1fr !important;
          }
          #guide-content .nav-button {
            padding: 8px 12px !important;
            font-size: 12px !important;
          }
          #guide-content h1 { font-size: 1.5em; }
          #guide-content h2 { font-size: 1.3em; }
          #guide-content h3 { font-size: 1.1em; }
          ${styleContent}
        `;

        guidePanel.appendChild(scopedStyle);

        // Remove script tags from body content
        const bodyWithoutScripts = bodyContent.replace(/<script[^>]*>[\s\S]*?<\/script>/gi, '');
        guideContent.innerHTML = bodyWithoutScripts;

        // Execute scripts in global context
        const scriptElement = document.createElement('script');
        scriptElement.id = guideScriptId;
        scriptElement.textContent = scriptContent;
        document.head.appendChild(scriptElement);

        console.log('Guide content loaded successfully');

        // Initialize the guide after scripts are loaded
        if ((window as any).initializeGuide) {
          (window as any).initializeGuide();
        }
      })
      .catch(error => {
        console.error('Failed to load guide content:', error);
        guideContent.innerHTML = '<p style="color: red; padding: 20px;">Failed to load testing guide content.</p>';
      });

    // Create the application panel (75% width, right side)
    const appPanel = document.createElement('div');
    appPanel.id = 'app-panel';
    appPanel.style.cssText = `
      width: 75%;
      height: 100%;
      background: white;
      position: relative;
      overflow: auto;
      contain: layout style;
    `;

    // Store references to original body children (actual DOM nodes, not HTML)
    const originalBodyChildren = Array.from(document.body.children);
    const originalStyles = document.body.style.cssText;
    const originalBodyClasses = document.body.className;

    // Create app content container
    const appContent = document.createElement('div');
    appContent.id = 'app-content';
    appContent.style.cssText = `
      width: 100%;
      height: 100%;
      position: relative;
      overflow: auto;
    `;

    // Move (not clone) the existing Angular app DOM nodes
    originalBodyChildren.forEach(child => {
      appContent.appendChild(child);
    });

    // Apply any necessary styles to maintain the app appearance
    const appStyles = document.createElement('style');
    appStyles.textContent = `
      #app-content {
        min-height: 100%;
      }
      #app-content .testing-notification-bar {
        display: none !important;
      }
      #app-panel router-outlet + * {
        display: block;
        height: 100%;
      }
      #app-content .row > .col > .container {
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)) !important;
      }
      #app-content .row > .col > .container > .card {
        display: block !important;
        text-decoration: none !important;
        height: auto !important;
      }
      #app-content .row > .col > .container > .card .card-img-wrapper {
        width: 100% !important;
        display: block !important;
      }
      #app-content .row > .col > .container > .card .card-body {
        width: 100% !important;
        display: block !important;
      }
      #app-content .row > .col > .container > .card img {
        width: 100% !important;
        display: block !important;
      }
    `;
    appPanel.appendChild(appStyles);

    // Create close button
    const closeButton = document.createElement('button');
    closeButton.innerHTML = '✕ Close Split Screen';
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

    // Simple close handler - just clear localStorage and reload
    const closeHandler = () => {
      // Remove split-screen state from localStorage
      localStorage.removeItem('testing-split-screen-active');

      // Reload the page to restore original state
      window.location.reload();
    };

    closeButton.addEventListener('click', closeHandler);

    // Add debug logging
    console.log('Testing close button added with event listener');

    // Also store the handler globally for emergency access
    (window as any).closeTesting = closeHandler;

    // Add onclick as backup (in case addEventListener fails)
    closeButton.onclick = closeHandler;

    // Create resize handle
    const resizeHandle = document.createElement('div');
    resizeHandle.style.cssText = `
      position: fixed;
      top: 0;
      left: calc(25% - 5px);
      width: 10px;
      height: 100vh;
      background: #007bff;
      cursor: ew-resize;
      z-index: 10002;
      transition: background 0.2s;
      border-radius: 0 5px 5px 0;
      opacity: 0.8;
    `;

    // Add a visual indicator
    resizeHandle.innerHTML = '<div style="width: 2px; height: 20px; background: white; margin: 50% auto 0; border-radius: 1px;"></div>';

    // Add hover effect
    resizeHandle.addEventListener('mouseenter', () => {
      if (!isResizing) {
        resizeHandle.style.background = '#0056b3';
        resizeHandle.style.opacity = '1';
      }
    });

    resizeHandle.addEventListener('mouseleave', () => {
      if (!isResizing) {
        resizeHandle.style.background = '#007bff';
        resizeHandle.style.opacity = '0.8';
      }
    });

    // Add resize functionality - improved version
    let isResizing = false;
    let startX = 0;
    let startWidth = 0;

    const handleMouseDown = (e: MouseEvent) => {
      isResizing = true;
      startX = e.clientX;
      startWidth = parseInt(window.getComputedStyle(guidePanel).width, 10);

      // Add visual feedback
      resizeHandle.style.background = '#0056b3';
      resizeHandle.style.opacity = '1';
      document.body.style.cursor = 'ew-resize';
      document.body.style.userSelect = 'none';

      // Prevent text selection and default behaviors
      e.preventDefault();
      e.stopPropagation();

      // Add global event listeners with capture to ensure we catch all events
      document.addEventListener('mousemove', handleMouseMove, { passive: false, capture: true });
      document.addEventListener('mouseup', handleMouseUp, { passive: false, capture: true });

      // Also listen on window to catch events that might escape the document
      window.addEventListener('mousemove', handleMouseMove, { passive: false });
      window.addEventListener('mouseup', handleMouseUp, { passive: false });
    };

    const handleMouseMove = (e: MouseEvent) => {
      if (!isResizing) return;

      e.preventDefault();
      e.stopPropagation();

      // Ensure we have valid coordinates
      if (typeof e.clientX !== 'number') return;

      const deltaX = e.clientX - startX;
      const newWidth = startWidth + deltaX;
      const containerWidth = splitContainer.offsetWidth;

      // Ensure we have a valid container width
      if (containerWidth <= 0) return;

      const percentage = Math.min(Math.max((newWidth / containerWidth) * 100, 15), 60);

      // Update panel widths with requestAnimationFrame for smoother resizing
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

      // Reset visual feedback
      resizeHandle.style.background = '#007bff';
      resizeHandle.style.opacity = '0.8';
      document.body.style.cursor = '';
      document.body.style.userSelect = '';

      // Remove all event listeners
      document.removeEventListener('mousemove', handleMouseMove, { capture: true } as any);
      document.removeEventListener('mouseup', handleMouseUp, { capture: true } as any);
      window.removeEventListener('mousemove', handleMouseMove);
      window.removeEventListener('mouseup', handleMouseUp);

      e.preventDefault();
      e.stopPropagation();
    };

    // Attach event listener
    resizeHandle.addEventListener('mousedown', handleMouseDown);

    // Also add touch support for tablets
    resizeHandle.addEventListener('touchstart', (e) => {
      const touch = e.touches[0];
      isResizing = true;
      startX = touch.clientX;
      startWidth = parseInt(window.getComputedStyle(guidePanel).width, 10);

      resizeHandle.style.background = '#0056b3';
      e.preventDefault();
    });

    const handleTouchMove = (e: TouchEvent) => {
      if (!isResizing) return;

      const touch = e.touches[0];
      const deltaX = touch.clientX - startX;
      const newWidth = startWidth + deltaX;
      const containerWidth = splitContainer.offsetWidth;
      const percentage = Math.min(Math.max((newWidth / containerWidth) * 100, 15), 60);

      guidePanel.style.width = percentage + '%';
      appPanel.style.width = (100 - percentage) + '%';
      resizeHandle.style.left = `calc(${percentage}% - 5px)`;

      e.preventDefault();
    };

    const handleTouchEnd = (e: TouchEvent) => {
      isResizing = false;
      resizeHandle.style.background = '#007bff';
      document.removeEventListener('touchmove', handleTouchMove);
      document.removeEventListener('touchend', handleTouchEnd);
      e.preventDefault();
    };

    document.addEventListener('touchmove', handleTouchMove, { passive: false });
    document.addEventListener('touchend', handleTouchEnd, { passive: false });

    // Assemble the split screen WITHOUT iframes
    guidePanel.appendChild(guideContent);
    appPanel.appendChild(appContent); // Use div content instead of iframe
    splitContainer.appendChild(guidePanel);
    splitContainer.appendChild(appPanel);
    // Don't add close button to container - add to body instead

    // Store original state
    (window as any).testingSplitScreen = {
      originalBodyChildren,
      originalStyles,
      originalBodyClasses,
      container: splitContainer,
      scriptId: guideScriptId
    };

    // Show split screen (body is already empty after moving children to appContent)
    document.body.style.cssText = 'margin: 0; padding: 0; overflow: hidden;';
    document.body.appendChild(splitContainer);
    document.body.appendChild(resizeHandle); // Add resize handle to body (fixed position)
    document.body.appendChild(closeButton); // Add close button to body

    // Handle escape key to close
    const handleEscape = (e: KeyboardEvent) => {
      if (e.key === 'Escape') {
        closeHandler(); // Use the same handler as the close button
      }
    };
    document.addEventListener('keydown', handleEscape);
    (window as any).testingSplitScreen.escapeHandler = handleEscape;

    // Re-initialize Angular app in the app panel if needed
    this.reinitializeAngularInPanel(appContent);
  }

  private reinitializeAngularInPanel(container: HTMLElement): void {
    // Since we're moving the Angular app content, we need to maintain event listeners
    // The best approach is to reload when closing to restore full Angular functionality

    // For now, hide the testing notification bar that would duplicate
    const testingBars = container.querySelectorAll('.testing-notification-bar');
    testingBars.forEach(bar => {
      (bar as HTMLElement).style.display = 'none';
    });

    // Ensure proper scrolling behavior
    container.style.overflowY = 'auto';
    container.style.height = '100%';
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

    // Store reference to the script element for cleanup (moved up here)
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

        // Extract styles if any
        const styles = doc.querySelectorAll('style');
        let styleContent = '';
        styles.forEach(style => {
          styleContent += style.textContent;
        });

        // Extract scripts and make them available globally
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
        // Don't wrap in IIFE so functions are globally accessible
        scriptElement.textContent = scriptContent;
        document.head.appendChild(scriptElement);

        console.log('Bug hunting guide content loaded successfully');

        // Initialize the bug hunting guide if function exists
        if ((window as any).initializeBugHunting) {
          (window as any).initializeBugHunting();
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

    // Store references to original body children (actual DOM nodes, not HTML)
    const originalBodyChildren = Array.from(document.body.children);
    const originalStyles = document.body.style.cssText;
    const originalBodyClasses = document.body.className;

    // Create app content container for bug hunting version
    const appContent = document.createElement('div');
    appContent.id = 'bug-app-content';
    appContent.style.cssText = `
      width: 100%;
      height: 100%;
      position: relative;
      overflow: auto;
    `;

    // For bug hunting, we'll load the current app but point to sprint5-with-bugs if available
    // Check if we're in a development environment with local access
    const isLocalDev = window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';

    if (isLocalDev) {
      // Move (not clone) the existing Angular app DOM nodes
      originalBodyChildren.forEach(child => {
        appContent.appendChild(child);
      });

      // Apply styles to indicate this is the bug hunting version
      const bugStyles = document.createElement('style');
      bugStyles.textContent = `
        #bug-app-content {
          min-height: 100%;
        }
        #bug-app-content .testing-notification-bar {
          display: none !important;
        }
        #bug-app-content::before {
          content: '🐛 BUG HUNTING MODE';
          position: fixed;
          top: 10px;
          left: 50%;
          transform: translateX(-50%);
          background: #dc3545;
          color: white;
          padding: 5px 15px;
          border-radius: 20px;
          font-size: 12px;
          z-index: 1000;
          font-weight: bold;
        }
        #bug-app-content .row > .col > .container {
          grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)) !important;
        }
        #bug-app-content .row > .col > .container > .card {
          display: block !important;
          text-decoration: none !important;
          height: auto !important;
        }
        #bug-app-content .row > .col > .container > .card .card-img-wrapper {
          width: 100% !important;
          display: block !important;
        }
        #bug-app-content .row > .col > .container > .card .card-body {
          width: 100% !important;
          display: block !important;
        }
        #bug-app-content .row > .col > .container > .card img {
          width: 100% !important;
          display: block !important;
        }
      `;
      appPanel.appendChild(bugStyles);
    } else {
      // For production, use iframe to load external bug site
      const appFrame = document.createElement('iframe');
      appFrame.src = 'https://with-bugs.practicesoftwaretesting.com';
      appFrame.style.cssText = `
        width: 100%;
        height: 100%;
        border: none;
        display: block;
      `;
      appContent.appendChild(appFrame);
    }

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

    // Simple close handler - just clear localStorage and reload
    const closeHandler = () => {
      // Remove split-screen state from localStorage
      localStorage.removeItem('bug-hunting-split-screen-active');

      // Reload the page to restore original state
      window.location.reload();
    };

    closeButton.addEventListener('click', closeHandler);

    // Add debug logging
    console.log('Bug hunting close button added with event listener');

    // Also store the handler globally for emergency access
    (window as any).closeBugHunting = closeHandler;

    // Add onclick as backup (in case addEventListener fails)
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

    // Add a visual indicator
    resizeHandle.innerHTML = '<div style="width: 2px; height: 20px; background: white; margin: 50% auto 0; border-radius: 1px;"></div>';

    // Add hover effect
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
    let isResizing = false;
    let startX = 0;
    let startWidth = 0;

    const handleMouseDown = (e: MouseEvent) => {
      isResizing = true;
      startX = e.clientX;
      startWidth = parseInt(window.getComputedStyle(guidePanel).width, 10);

      // Add visual feedback
      resizeHandle.style.background = '#c82333';
      resizeHandle.style.opacity = '1';
      document.body.style.cursor = 'ew-resize';
      document.body.style.userSelect = 'none';

      // Prevent text selection and default behaviors
      e.preventDefault();
      e.stopPropagation();

      // Add global event listeners
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

      // Reset visual feedback
      resizeHandle.style.background = '#dc3545';
      resizeHandle.style.opacity = '0.8';
      document.body.style.cursor = '';
      document.body.style.userSelect = '';

      // Remove all event listeners
      document.removeEventListener('mousemove', handleMouseMove, { capture: true } as any);
      document.removeEventListener('mouseup', handleMouseUp, { capture: true } as any);
      window.removeEventListener('mousemove', handleMouseMove);
      window.removeEventListener('mouseup', handleMouseUp);

      e.preventDefault();
      e.stopPropagation();
    };

    // Attach event listener
    resizeHandle.addEventListener('mousedown', handleMouseDown);

    // Assemble the split screen WITHOUT iframes for guide
    guidePanel.appendChild(guideContent);
    appPanel.appendChild(appContent); // Use content container
    splitContainer.appendChild(guidePanel);
    splitContainer.appendChild(appPanel);
    // Don't add close button to container - add to body instead

    // Store original state
    (window as any).bugHuntingSplitScreen = {
      originalBodyChildren,
      originalStyles,
      originalBodyClasses,
      container: splitContainer,
      scriptId: bugScriptId
    };

    // Show split screen (body is already empty after moving children to appContent)
    document.body.style.cssText = 'margin: 0; padding: 0; overflow: hidden;';
    document.body.appendChild(splitContainer);
    document.body.appendChild(resizeHandle); // Add resize handle to body (fixed position)
    document.body.appendChild(closeButton); // Add close button to body

    // Handle escape key to close
    const handleEscape = (e: KeyboardEvent) => {
      if (e.key === 'Escape') {
        closeHandler(); // Use the same handler as the close button
      }
    };
    document.addEventListener('keydown', handleEscape);
    (window as any).bugHuntingSplitScreen.escapeHandler = handleEscape;
  }

  private closeBugHuntingSplitScreenMode(): void {
    // Remove split-screen state from localStorage
    localStorage.removeItem('bug-hunting-split-screen-active');

    // Reload the page to restore original state
    window.location.reload();
  }

  private closeSplitScreenMode(): void {
    // Remove split-screen state from localStorage
    localStorage.removeItem('testing-split-screen-active');

    // Reload the page to restore original state
    window.location.reload();
  }

}
