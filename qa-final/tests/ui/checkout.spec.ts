import { test, expect, Page } from '@playwright/test';

test.use({ locale: 'en-US' });

const CUSTOMER_EMAIL = 'customer@practicesoftwaretesting.com';
const CUSTOMER_PASSWORD = 'welcome01';
const PRODUCTO = 'Combination Pliers';
const PRECIO = 14.15;
const TOTAL_CARRITO = `$${PRECIO.toFixed(2)}`;

const DIRECCION = {
  country: 'CO',
  postal_code: '760001',
  house_number: '10',
};

const TARJETA = {
  number: '4111-1111-1111-1111',
  expiration: '12/2030',
  cvv: '123',
  holder: 'Iker Mainguez',
};

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

async function completarDireccion(page: Page) {
  await page.getByTestId('country').selectOption(DIRECCION.country);
  await page.getByTestId('postal_code').fill(DIRECCION.postal_code);
  await page.getByTestId('house_number').fill(DIRECCION.house_number);

  await expect(page.getByTestId('postcode-lookup-loading')).toHaveCount(0, { timeout: 10_000 });
  await expect(page.getByTestId('postcode-lookup-error')).toHaveCount(0);
  await expect(page.getByTestId('street')).not.toBeEmpty();
  await expect(page.getByTestId('city')).not.toBeEmpty();

  await expect(page.getByTestId('proceed-3')).toBeEnabled();
  await page.getByTestId('proceed-3').click();
}

test('TC-CHK-001 Compra E2E con tarjeta de crédito', async ({ page }) => {
  await iniciarSesion(page);
  await agregarProductoAlCarrito(page);

  await page.getByTestId('nav-cart').click();
  await expect(page.getByTestId('product-title')).toContainText(PRODUCTO);
  await expect(page.getByTestId('cart-total')).toHaveText(TOTAL_CARRITO);
  await page.getByTestId('proceed-1').click();

  await expect(page.getByTestId('proceed-2')).toBeVisible();
  await page.getByTestId('proceed-2').click();

  await completarDireccion(page);

  await page.getByTestId('payment-method').selectOption('credit-card');
  await page.getByTestId('credit_card_number').fill(TARJETA.number);
  await page.getByTestId('expiration_date').fill(TARJETA.expiration);
  await page.getByTestId('cvv').fill(TARJETA.cvv);
  await page.getByTestId('card_holder_name').fill(TARJETA.holder);

  const pago = page.waitForResponse(
    (r) => r.url().includes('/payment/check') && r.request().method() === 'POST',
  );
  await page.getByTestId('finish').click();
  const respPago = await pago;
  expect(respPago.status()).toBe(200);
  expect((await respPago.json()).message).toBe('Payment was successful');
  await expect(page.getByTestId('payment-success-message')).toHaveText('Payment was successful');
  await page.screenshot({ path: 'evidence/TC-CHK-001-pago-exitoso.png', fullPage: true });

  const factura = page.waitForResponse(
    (r) => new URL(r.url()).pathname === '/invoices' && r.request().method() === 'POST',
  );
  const carritoBorrado = page.waitForResponse(
    (r) => /\/carts\/[^/?]+$/.test(new URL(r.url()).pathname) && r.request().method() === 'DELETE',
  );
  await page.getByTestId('finish').click();
  const respFactura = await factura;
  expect(respFactura.status()).toBe(201);
  const body = await respFactura.json();
  const invoiceNumber = body.invoice_number as string;
  expect(invoiceNumber).toMatch(/^INV-/);

  await expect(page.locator('#order-confirmation')).toBeVisible();
  await expect(page.locator('#order-confirmation')).toContainText(invoiceNumber);

  await carritoBorrado;
  await expect(page.getByTestId('cart-quantity')).toHaveCount(0);

  const totalFactura = `$${Number(body.total).toFixed(2)}`;
  await page.getByTestId('nav-menu').click();
  await page.getByTestId('nav-my-invoices').click();
  await expect(page.getByTestId('page-title')).toHaveText('Invoices');
  const fila = page.locator('table tbody tr').filter({ hasText: invoiceNumber });
  await expect(fila).toBeVisible();
  await expect(fila.locator('td').nth(3)).toHaveText(totalFactura);
  expect(totalFactura).toBe(TOTAL_CARRITO);
});
