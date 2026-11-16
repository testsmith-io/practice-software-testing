import { APIRequestContext } from '@playwright/test';
import { authenticator } from 'otplib';
import { API_URL } from '../../config/env';

// Preparación de datos por API. Aquí no hay assertions: si una precondición falla, el test se
// detiene con un error que dice qué paso de la preparación falló.

export const PASSWORD = 'Tq7#vLm2-Ex26';
export const WRONG_PASSWORD = 'Wrong#Pass1';

async function ok(res: Awaited<ReturnType<APIRequestContext["post"]>>, paso: string): Promise<any> {
  if (!res.ok()) throw new Error(`Precondición fallida (${paso}): HTTP ${res.status()} ${await res.text()}`);
  return res.json();
}

/** Registra un usuario nuevo (rol user) y devuelve su correo. */
export async function registerUser(request: APIRequestContext, prefix: string): Promise<string> {
  const email = `${prefix}.${Date.now()}@toolshop.test`;
  await ok(
    await request.post(`${API_URL}/users/register`, {
      data: { first_name: 'QA', last_name: 'Final', email, password: PASSWORD },
    }),
    `registrar ${email}`,
  );
  return email;
}

/** Devuelve un código TOTP que no vence en los próximos segundos. */
export async function totpCode(secret: string): Promise<string> {
  if (authenticator.timeRemaining() < 5) {
    await new Promise((r) => setTimeout(r, (authenticator.timeRemaining() + 1) * 1000));
  }
  return authenticator.generate(secret);
}

/** Activa TOTP para el usuario (POST /totp/setup y /totp/verify) y devuelve el secreto. */
export async function enableTotp(request: APIRequestContext, email: string): Promise<string> {
  const login = await ok(
    await request.post(`${API_URL}/users/login`, { data: { email, password: PASSWORD } }),
    'login para activar TOTP',
  );
  const headers = { Authorization: `Bearer ${login.access_token}` };
  const setup = await ok(await request.post(`${API_URL}/totp/setup`, { headers }), 'POST /totp/setup');
  await ok(
    await request.post(`${API_URL}/totp/verify`, { headers, data: { totp: await totpCode(setup.secret) } }),
    'POST /totp/verify',
  );
  return setup.secret;
}
