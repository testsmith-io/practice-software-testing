import { test, expect, Page } from '@playwright/test';
import { registerUser, enableTotp, totpCode, PASSWORD, WRONG_PASSWORD } from '../support/api';

// Casos de Login. Diseño: docs/diseno-catalogo-login.md, secciones 2.4, 2.5 y 3.
// Cada caso registra su propio usuario por API, así no se bloquean las cuentas sembradas.
test.use({ locale: 'en-US' });

const MENSAJE_BLOQUEO = 'Account locked, too many failed attempts. Please contact the administrator.';

/** Llena el formulario, envía y devuelve el código HTTP de POST /users/login. */
async function intentarLogin(page: Page, email: string, password: string): Promise<number> {
  await page.getByTestId('email').fill(email);
  await page.getByTestId('password').fill(password);
  const respuesta = page.waitForResponse((r) => r.url().endsWith('/users/login') && r.request().method() === 'POST');
  await page.getByTestId('login-submit').click();
  return (await respuesta).status();
}

// ---------------------------------------------------------------------------
// TC-LOG-001: transición de estados + valores límite 2, 3 y 4 + ramas D2, D3 y D4
// ---------------------------------------------------------------------------
const SUBCASOS = [
  { id: 'a', fallos: 2, ultimo: PASSWORD, nombre: '2 fallos y luego contraseña correcta: entra' },
  { id: 'b', fallos: 3, ultimo: PASSWORD, nombre: '3 fallos y luego contraseña correcta: 423' },
  { id: 'c', fallos: 3, ultimo: WRONG_PASSWORD, nombre: '4.º fallo: 423' },
];

test.describe('TC-LOG-001 Bloqueo de cuenta al pasar el umbral de 3 intentos fallidos', () => {
  for (const s of SUBCASOS) {
    test(`TC-LOG-001 ${s.id}) ${s.nombre}`, async ({ page, request }) => {
      const email = await registerUser(request, `qa.log001.${s.id}`);
      await page.goto('/auth/login');

      for (let i = 1; i <= s.fallos; i++) {
        const codigo = await intentarLogin(page, email, WRONG_PASSWORD);
        // Esperado: cada fallo antes del bloqueo responde 401
        expect(codigo, `fallo ${i}`).toBe(401);
        // Esperado: cada fallo antes del bloqueo muestra "Invalid email or password"
        await expect(page.getByTestId('login-error')).toHaveText('Invalid email or password');
      }

      const codigo = await intentarLogin(page, email, s.ultimo);

      if (s.id === 'a') {
        // Esperado a2: con la contraseña correcta la API responde 200
        expect(codigo).toBe(200);
        // Esperado a2: va a /account y sale el título "My account"
        await expect(page).toHaveURL(/\/account$/);
        await expect(page.getByTestId('page-title')).toHaveText('My account');
      } else {
        // Esperado b2 y c2: la API responde 423
        expect(codigo).toBe(423);
        // Esperado b2 y c2: sale el mensaje de cuenta bloqueada
        await expect(page.getByTestId('login-error')).toHaveText(MENSAJE_BLOQUEO);
        // Esperado b2: la URL sigue en /auth/login
        await expect(page).toHaveURL(/\/auth\/login$/);
      }
    });
  }
});

// ---------------------------------------------------------------------------
// TC-LOG-002 y TC-LOG-003: caja blanca, ramas D6 y D10
// ---------------------------------------------------------------------------
test('TC-LOG-002 Un código TOTP inválido se rechaza y el usuario no entra', async ({ page, request }) => {
  const email = await registerUser(request, 'qa.log002');
  const secreto = await enableTotp(request, email);
  await page.goto('/auth/login');

  const codigoLogin = await intentarLogin(page, email, PASSWORD);
  // Esperado 1: la API responde 200 y aparece el campo TOTP Code
  expect(codigoLogin).toBe(200);
  await expect(page.getByTestId('totp-code')).toBeVisible();

  // Código inválido: el válido + 1 (módulo 1 000 000), así nunca coincide por suerte
  const valido = await totpCode(secreto);
  const invalido = String((Number(valido) + 1) % 1_000_000).padStart(6, '0');
  await page.getByTestId('totp-code').fill(invalido);
  const respuesta = page.waitForResponse((r) => r.url().endsWith('/users/login') && r.request().method() === 'POST');
  await page.getByTestId('verify-totp').click();

  // Esperado 2: sale "Invalid TOTP" y la API responde 401
  expect((await respuesta).status()).toBe(401);
  await expect(page.getByTestId('login-error')).toHaveText('Invalid TOTP');
  // Esperado 3: la URL sigue en /auth/login
  await expect(page).toHaveURL(/\/auth\/login$/);
});

test('TC-LOG-003 Con contraseña correcta y código TOTP válido el usuario entra a su cuenta', async ({ page, request }) => {
  const email = await registerUser(request, 'qa.log003');
  const secreto = await enableTotp(request, email);
  await page.goto('/auth/login');

  const codigoLogin = await intentarLogin(page, email, PASSWORD);
  // Esperado 1: aparece el campo TOTP Code (200)
  expect(codigoLogin).toBe(200);
  await expect(page.getByTestId('totp-code')).toBeVisible();

  // La UI navega a /account apenas recibe el token, así que la respuesta se captura al pasar por el navegador
  let capturar!: (v: { status: number; body: Record<string, unknown> }) => void;
  const verificacion = new Promise<{ status: number; body: Record<string, unknown> }>((res) => (capturar = res));
  await page.route('**/users/login', async (route) => {
    const resp = await route.fetch();
    capturar({ status: resp.status(), body: await resp.json() });
    await route.fulfill({ response: resp });
  });
  await page.getByTestId('totp-code').fill(await totpCode(secreto));
  await page.getByTestId('verify-totp').click();
  const { status, body } = await verificacion;

  // Esperado 2: la API responde 200 con un access_token
  expect(status).toBe(200);
  expect(body).toHaveProperty('access_token');
  // Esperado 3: va a /account y sale el título "My account"
  await expect(page).toHaveURL(/\/account$/);
  await expect(page.getByTestId('page-title')).toHaveText('My account');
  // Esperado 4: no sale ningún mensaje de error
  await expect(page.getByTestId('login-error')).toHaveCount(0);
});
