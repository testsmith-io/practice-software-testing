import { test, expect, Page } from '@playwright/test';
import { registerUser, PASSWORD, WRONG_PASSWORD } from '../support/api';

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
