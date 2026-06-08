# Ingredient API Surface

## Current Runtime State

The only active ingredient API surface is the Symfony controller surface:

| Method | Path | Route name | Responsible class |
| --- | --- | --- | --- |
| POST | `/ingredients/create` | `createIngredients` | `App\Controller\IngredientController::createIngredientsAction` |
| GET | `/ingredients` | `getIngredientsList` | `App\Controller\IngredientController::getIngredientsListAction` |
| PUT | `/ingredients` | `updateIngredients` | `App\Controller\IngredientController::updateIngredientsAction` |
| GET | `/ingredients/single/{name}` | `getSingleIngredientByName` | `App\Controller\IngredientController::getSingleIngredientsByNameAction` |
| GET | `/ingredients/{name}` | `getMultipleIngredientByName` | `App\Controller\IngredientController::getMultipleIngredientsByNameAction` |
| DELETE | `/ingredients/single/{id}` | `deleteSingleIngredientById` | `App\Controller\IngredientController::deleteSingleIngredientByIdAction` |
| DELETE | `/ingredients/{name}` | `deleteMultipleIngredientsByName` | `App\Controller\IngredientController::deleteMultipleIngredientsByNameAction` |

There is currently no active `/api/ingredients` route:

- `ApiPlatformBundle` is not registered in `config/bundles.php`.
- `config/routes/api_platform.yaml` is not present.
- `App\Entity\Ingredient` is not an `ApiResource`.

## Official Surface Decision

For now, `/ingredients` is the current active and testable ingredient API surface.

The product direction is to make API Platform the official public API surface progressively, under `/api/ingredients`, but that route does not exist yet. It should not be documented as active until it is reintroduced and covered by functional tests.

## Known Inconsistencies

- `POST /ingredients/create` is a bulk creation endpoint, but the path is not REST-standard.
- `GET /ingredients/{name}` performs a partial name search.
- `DELETE /ingredients/{name}` deletes all partial name matches, which is risky.
- `GET /ingredients/single/{name}` uses `single` with a name, while `DELETE /ingredients/single/{id}` uses `single` with an id.
- Responses and validation are manually handled by the controller, not normalized by API Platform.

## Migration Strategy

1. Keep `/ingredients` unchanged until the behavior is covered by functional tests.
2. Add focused functional tests for the current `/ingredients` surface.
3. Reintroduce `/api/ingredients` through API Platform in a dedicated ticket.
4. Compare behavior between `/ingredients` and `/api/ingredients`.
5. Mark `/ingredients` as legacy only after `/api/ingredients` exists and is tested.

