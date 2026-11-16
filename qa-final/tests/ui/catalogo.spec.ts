import { test, expect, Page } from '@playwright/test';

// Casos de Catálogo. Diseño: docs/diseno-catalogo-login.md, secciones 2.1 a 2.3 y 3.
// Precondición de todos: base sembrada con migrate:fresh --seed y navegador en inglés.
test.use({ locale: 'en-US' });

const PRODUCTOS_POR_PAGINA = 9;

/** Lee nombres y precios de las tarjetas visibles. */
async function leerTarjetas(page: Page) {
  const nombres = (await page.getByTestId('product-name').allInnerTexts()).map((n) => n.trim());
  const precios = (await page.getByTestId('product-price').allInnerTexts()).map((p) => Number(p.replace('$', '')));
  return { nombres, precios };
}

/** Ejecuta una acción y espera la respuesta de /products que dispara, para no leer la cuadrícula a medias. */
async function conRespuesta(page: Page, accion: () => Promise<unknown>) {
  const respuesta = page.waitForResponse((r) => new URL(r.url()).pathname === '/products' && r.request().method() !== 'OPTIONS');
  await accion();
  await respuesta;
}

function botonesDePagina(page: Page) {
  return page.getByRole('button', { name: /^Page-\d+$/ });
}

// ---------------------------------------------------------------------------
// TC-CAT-001: tabla de decisión con pairwise (reglas R1 a R9 de la sección 2.1)
// ---------------------------------------------------------------------------
type Regla = {
  id: string;
  categoria?: string;
  marca?: string;
  precio: [number, number];
  orden: 'Price (Low - High)' | 'Name (Z - A)';
  esperados: string[];
  total: number;
};

const REGLAS: Regla[] = [
  { id: 'R1', categoria: 'Hammer', marca: 'ForgeFlex Tools', precio: [1, 100], orden: 'Price (Low - High)', total: 6,
    esperados: ['Thor Hammer', 'Hammer', 'Claw Hammer with Shock Reduction Grip', 'Sledgehammer', 'Court Hammer', 'Claw Hammer with Fiberglass Handle'] },
  { id: 'R2', categoria: 'Hammer', marca: 'MightyCraft Hardware', precio: [10, 20], orden: 'Name (Z - A)', total: 1,
    esperados: ['Claw Hammer'] },
  { id: 'R3', categoria: 'Hammer', precio: [1, 100], orden: 'Price (Low - High)', total: 7,
    esperados: ['Thor Hammer', 'Claw Hammer', 'Hammer', 'Claw Hammer with Shock Reduction Grip', 'Sledgehammer', 'Court Hammer', 'Claw Hammer with Fiberglass Handle'] },
  { id: 'R4', categoria: 'Pliers', marca: 'ForgeFlex Tools', precio: [10, 20], orden: 'Price (Low - High)', total: 2,
    esperados: ['Pliers', 'Combination Pliers'] },
  { id: 'R5', categoria: 'Pliers', marca: 'MightyCraft Hardware', precio: [1, 100], orden: 'Price (Low - High)', total: 3,
    esperados: ['Slip Joint Pliers', 'Long Nose Pliers', 'Bolt Cutters'] },
  { id: 'R6', categoria: 'Pliers', precio: [1, 100], orden: 'Name (Z - A)', total: 5,
    esperados: ['Slip Joint Pliers', 'Pliers', 'Long Nose Pliers', 'Combination Pliers', 'Bolt Cutters'] },
  { id: 'R7', marca: 'ForgeFlex Tools', precio: [1, 100], orden: 'Name (Z - A)', total: 36,
    esperados: ['Wood Saw', 'Wood Carving Chisels', 'Washers', 'Tool Cabinet', 'Thor Hammer', 'Tape Measure 7.5m', 'Tape Measure 5m', 'Swiss Woodcarving Chisels', 'Super-thin Protection Gloves'] },
  { id: 'R8', marca: 'MightyCraft Hardware', precio: [1, 100], orden: 'Price (Low - High)', total: 9,
    esperados: ['Screws', 'Slip Joint Pliers', 'Claw Hammer', 'Chisels Set', 'Long Nose Pliers', 'Cordless Drill 12V', 'Bolt Cutters', 'Belt Sander', 'Drawer Tool Cabinet'] },
  { id: 'R9', precio: [10, 20], orden: 'Price (Low - High)', total: 17,
    esperados: ['Measuring Tape', 'Thor Hammer', 'Claw Hammer', 'Pliers', 'Wood Saw', 'Hammer', 'Tape Measure 5m', 'Chisels Set', 'Claw Hammer with Shock Reduction Grip'] },
];

/** Lleva un control del slider a un valor con el teclado (flechas = 1, PageUp/PageDown = salto grande). */
async function moverControl(page: Page, control: ReturnType<Page['locator']>, objetivo: number) {
  await control.focus();
  for (let i = 0; i < 200; i++) {
    const actual = Number(await control.getAttribute('aria-valuenow'));
    if (actual === objetivo) return;
    const d = objetivo - actual;
    const tecla = Math.abs(d) >= 20 ? (d > 0 ? 'PageUp' : 'PageDown') : d > 0 ? 'ArrowRight' : 'ArrowLeft';
    await conRespuesta(page, () => page.keyboard.press(tecla));
  }
  throw new Error(`No se pudo llevar el slider a ${objetivo}`);
}

async function ajustarPrecio(page: Page, [min, max]: [number, number]) {
  if (min === 1 && max === 100) return; // rango por defecto, no se toca
  await moverControl(page, page.locator('ngx-slider .ngx-slider-pointer-max'), max);
  await moverControl(page, page.locator('ngx-slider .ngx-slider-pointer-min'), min);
}

test.describe('TC-CAT-001 Filtros combinados: categoría, marca, precio y orden', () => {
  for (const r of REGLAS) {
    test(`TC-CAT-001 ${r.id}: ${r.categoria ?? 'sin categoría'} + ${r.marca ?? 'sin marca'} + $${r.precio[0]}-$${r.precio[1]} + ${r.orden}`, async ({ page }) => {
      await page.goto('/');
      await page.getByTestId('product-name').first().waitFor(); // espera a que cargue el catálogo

      if (r.categoria) await conRespuesta(page, () => page.getByRole('checkbox', { name: r.categoria, exact: true }).check());
      if (r.marca) await conRespuesta(page, () => page.getByRole('checkbox', { name: r.marca!, exact: true }).check());
      await ajustarPrecio(page, r.precio);
      await conRespuesta(page, () => page.getByTestId('sort').selectOption({ label: r.orden }));

      // Esperado 1: la página 1 muestra exactamente los productos de la regla (máximo 9)
      await expect(page.getByTestId('product-name')).toHaveText(r.esperados);

      const { nombres, precios } = await leerTarjetas(page);
      // Esperado 2: todos los precios mostrados están dentro del rango de la regla
      // (categoría y marca ya quedan verificadas con la lista exacta del esperado 1)
      expect(precios.every((p) => p >= r.precio[0] && p <= r.precio[1])).toBe(true);

      // Esperado 3: los productos van en el orden de la regla
      if (r.orden === 'Price (Low - High)') {
        expect(precios).toEqual([...precios].sort((a, b) => a - b));
      } else {
        expect(nombres).toEqual([...nombres].sort((a, b) => b.localeCompare(a)));
      }

      // Esperado 4: hay ⌈total / 9⌉ páginas; con una sola página no se muestra la paginación
      const paginas = Math.ceil(r.total / PRODUCTOS_POR_PAGINA);
      await expect(botonesDePagina(page)).toHaveCount(paginas > 1 ? paginas : 0);
    });
  }
});
