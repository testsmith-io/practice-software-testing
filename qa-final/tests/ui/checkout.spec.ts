import { test, expect, Page } from '@playwright/test';

// Casos de Checkout (Flujo B, CB-1 a CB-5).
// Precondiciones: app en localhost:4200, BD sembrada (migrate:fresh --seed), locale en-US.
test.use({ locale: 'en-US' });

const CUSTOMER_EMAIL = 'customer@practicesoftwaretesting.com';
const CUSTOMER_PASSWORD = 'welcome01'; // UserSeeder
const PRODUCTO = 'Combination Pliers';
const PRECIO = 14.15;
// Motivación de código: InvoiceService / CartComponent — >50% eco (A/B) aplica 5%
const TOTAL_ESPERADO = Number((PRECIO * 0.95).toFixed(2)); // 13.44

const DIRECCION = {
  country: 'CO',
  postal_code: '760001',
  // † house_number no está en el caso de diseño; el formulario lo exige (address.component.ts)
  house_number: '10',
  street: 'Calle 5 #10-20',
  city: 'Cali',
  state: 'Valle',
};

const TARJETA = {
  number: '4111-1111-1111-1111',
  expiration: '12/2030',
  cvv: '123',
  holder: 'Iker Mainguez',
};

/** Inicia sesión con el usuario semilla y espera llegar a /account. */
async function iniciarSesion(page: Page) {
  await page.goto('/auth/login');
  await page.getByTestId('email').fill(CUSTOMER_EMAIL);
  await page.getByTestId('password').fill(CUSTOMER_PASSWORD);
  const respuesta = page.waitForResponse(
    (r) => r.url().endsWith('/users/login') && r.request().method() === 'POST',
  );
  await page.getByTestId('login-submit').click();
  expect((await respuesta).status()).toBe(200);
  await expect(page).toHaveURL(/\/account$/);
}

/** Abre Combination Pliers y lo agrega al carrito (cantidad 1). */
async function agregarProductoAlCarrito(page: Page) {
  await page.goto('/');
  await page.getByTestId('product-name').first().waitFor();
  await page.getByTestId('product-name').filter({ hasText: PRODUCTO }).click();
  await expect(page.getByTestId('product-name')).toHaveText(PRODUCTO);

  const respuesta = page.waitForResponse(
    (r) => r.url().includes('/carts/') && r.request().method() === 'POST',
  );
  await page.getByTestId('add-to-cart').click();
  expect((await respuesta).ok()).toBeTruthy();
  await expect(page.getByTestId('cart-quantity')).toHaveText('1');
}

/**
 * Completa la dirección de facturación.
 * El lookup de CP puede sobrescribir street/city/state: se espera a que termine y luego se fijan los datos del caso.
 */
async function completarDireccion(page: Page) {
  await page.getByTestId('country').selectOption(DIRECCION.country);
  await page.getByTestId('postal_code').fill(DIRECCION.postal_code);
  await page.getByTestId('house_number').fill(DIRECCION.house_number);
  await expect(page.getByTestId('postcode-lookup-loading')).toHaveCount(0, { timeout: 10_000 });

  await page.getByTestId('street').fill(DIRECCION.street);
  await page.getByTestId('city').fill(DIRECCION.city);
  await page.getByTestId('state').fill(DIRECCION.state);

  await expect(page.getByTestId('proceed-3')).toBeEnabled();
  await page.getByTestId('proceed-3').click();
}

// ---------------------------------------------------------------------------
// TC-CHK-001: partición de equivalencia válida (flujo principal / smoke)
// ---------------------------------------------------------------------------
test('TC-CHK-001 Compra E2E con tarjeta de crédito', async ({ page }) => {
  // Paso 1: iniciar sesión
  await iniciarSesion(page);

  // Paso 2: agregar Combination Pliers x1
  await agregarProductoAlCarrito(page);

  // Paso 3: carrito → Proceed to checkout
  await page.getByTestId('nav-cart').click();
  await expect(page.getByTestId('product-title')).toContainText(PRODUCTO);
  // Esperado auxiliar: total del carrito con eco-descuento 5%
  await expect(page.getByTestId('cart-total')).toHaveText(`$${TOTAL_ESPERADO.toFixed(2)}`);
  await page.getByTestId('proceed-1').click();

  // Paso 4: identificación (ya autenticado) → Proceed to checkout
  await expect(page.getByTestId('proceed-2')).toBeVisible();
  await page.getByTestId('proceed-2').click();

  // Paso 5: dirección de facturación
  await completarDireccion(page);

  // Paso 6: Credit Card + datos
  await page.getByTestId('payment-method').selectOption('credit-card');
  await page.getByTestId('credit_card_number').fill(TARJETA.number);
  await page.getByTestId('expiration_date').fill(TARJETA.expiration);
  await page.getByTestId('cvv').fill(TARJETA.cvv);
  await page.getByTestId('card_holder_name').fill(TARJETA.holder);

  // Paso 7a: primer clic = Check payment (payment.component.ts: checkPayment)
  const pago = page.waitForResponse(
    (r) => r.url().includes('/payment/check') && r.request().method() === 'POST',
  );
  await page.getByTestId('finish').click();
  const respPago = await pago;
  // Esperado 3: API de pago exitosa (2xx)
  expect(respPago.status()).toBe(200);
  expect((await respPago.json()).message).toBe('Payment was successful');
  // Esperado 1: mensaje de pago exitoso
  await expect(page.getByTestId('payment-success-message')).toHaveText('Payment was successful');
  // Evidencia del caso: captura del mensaje de pago exitoso (antes del 2.º clic)
  await page.screenshot({ path: 'evidence/TC-CHK-001-pago-exitoso.png', fullPage: true });

  // Paso 7b: segundo clic = Confirm + POST /invoices (finishFunction cuando state === true)
  const factura = page.waitForResponse(
    (r) => new URL(r.url()).pathname === '/invoices' && r.request().method() === 'POST',
  );
  await page.getByTestId('finish').click();
  const respFactura = await factura;
  expect(respFactura.status()).toBe(201);
  const body = await respFactura.json();
  const invoiceNumber = body.invoice_number as string;
  expect(invoiceNumber).toMatch(/^INV-/);

  // Esperado 2: se muestra el número de factura
  await expect(page.locator('#order-confirmation')).toBeVisible();
  await expect(page.locator('#invoice-number')).toHaveText(invoiceNumber);

  // Postcondición: carrito vacío
  await expect(page.getByTestId('cart-quantity')).toHaveText('0');

  // Esperado 4: Mi cuenta > Invoices con total igual al del carrito
  await page.getByTestId('nav-menu').click();
  await page.getByTestId('nav-my-invoices').click();
  await expect(page.getByTestId('page-title')).toHaveText('Invoices');
  const fila = page.locator('table tbody tr').filter({ hasText: invoiceNumber });
  await expect(fila).toBeVisible();
  await expect(fila.locator('td').nth(3)).toHaveText(`$${TOTAL_ESPERADO.toFixed(2)}`);
});
