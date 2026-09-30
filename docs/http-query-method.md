# HTTP QUERY Method (sprint 5)

Sprint 5 supports the **HTTP QUERY method**
([RFC 10008](https://datatracker.ietf.org/doc/rfc10008/)) on its search and
filter endpoints. QUERY closes the gap between GET and POST: it is **safe and
idempotent** like GET, but carries its criteria in a **JSON request body**
instead of a URL query string — so complex filters no longer need to be
URL-encoded into `?between=price,10,50&by_category=1,2,3&...`.

The equivalent GET endpoints keep working unchanged; QUERY is offered
**alongside** them with identical behavior and results.

## Supported endpoints

| Endpoint            | Criteria (JSON body keys)                                                                        |
|---------------------|--------------------------------------------------------------------------------------------------|
| `QUERY /products`   | `q`, `page`, `sort`, `between`, `by_category`, `by_category_slug`, `by_brand`, `eco_friendly`, `is_rental`, `by_spec` |
| `QUERY /products/search` | `q`                                                                                          |
| `QUERY /brands/search`   | `q`                                                                                          |
| `QUERY /categories/search` | `q`                                                                                        |
| `QUERY /categories/tree` | `by_category_slug`                                                                           |
| `QUERY /invoices/search` | `q`, `page` *(requires authentication)*                                                      |
| `QUERY /users/search`    | `q`, `page` *(requires admin)*                                                               |

Body keys are **identical to the GET query parameters**, so any GET request
can be translated one-to-one:

```
GET /products?by_category=1,2&between=price,10,50&sort=price,asc&page=1
```

```
QUERY /products
Content-Type: application/json

{
  "by_category": "1,2",
  "between": "price,10,50",
  "sort": "price,asc",
  "page": "1"
}
```

Try it with curl:

```sh
curl -X QUERY http://localhost:8091/products \
  -H "Content-Type: application/json" \
  -d '{"q": "hammer", "between": "price,10,50"}'
```

## Request/response contract

- Requests **must** send `Content-Type: application/json`; anything else is
  rejected with **415 Unsupported Media Type** (an RFC 10008 MUST).
- Successful responses return **200** with the same payload as the GET
  equivalent.
- Responses carry an `Accept-Query: application/json` header advertising
  QUERY support and the accepted query format.
- QUERY is not a CORS-safelisted method, so browsers send an `OPTIONS`
  preflight first; the API's CORS configuration allows it.
- Every endpoint also answers a direct (non-preflight) `OPTIONS` request —
  e.g. `curl -X OPTIONS http://localhost:8091/products` — with **204 No
  Content** and an `Allow` header listing its supported methods, including
  `QUERY` where applicable.

## Where it is implemented

1. **Middleware** — `app/Http/Middleware/HandleQueryMethod.php` (alias
   `query.body`) validates the content type, merges the JSON body into the
   request's query bag (so controllers read QUERY criteria exactly like a GET
   query string), and sets the `Accept-Query` response header. Controllers and
   services are untouched.

2. **Routes** — `routes/api.php` registers a QUERY twin next to each
   parameterized GET route via `Route::match(['QUERY'], ...)`.

3. **Angular UI** — the search/filter methods in `product.service.ts`,
   `brand.service.ts`, `category.service.ts`, `invoice.service.ts`, and
   `user.service.ts` use `httpClient.request('QUERY', url, { body })`. Calls
   without criteria (e.g. the category tree without a slug) remain plain GET.

## Testing notes

This is a deliberately modern feature to practice against — most tools need a
*custom method* escape hatch rather than a named helper:

- **Postman**: type `QUERY` directly into the method dropdown (it accepts
  custom verbs).
- **RestAssured**: `given().body(...).request("QUERY", "/products")`.
- **Playwright**: `request.fetch(url, { method: 'QUERY', data: {...} })`.
- **Laravel/Pest**: `$this->json('QUERY', '/products', [...])` — see
  `tests/Feature/QueryMethodTest.php` for the full contract.

The generated Swagger documentation is emitted as **OpenAPI 3.2.0**, which
supports QUERY as a first-class operation. Each QUERY endpoint is annotated
with `@OA\Query(...)` in its controller (swagger-php 6.3 / l5-swagger 11) and
appears in the spec as a `query` operation with a JSON request-body schema,
next to its GET twin.
