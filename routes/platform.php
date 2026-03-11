<?php

declare(strict_types=1);

use App\Orchid\Screens\Examples\ExampleActionsScreen;
use App\Orchid\Screens\Examples\ExampleCardsScreen;
use App\Orchid\Screens\Examples\ExampleChartsScreen;
use App\Orchid\Screens\Examples\ExampleFieldsAdvancedScreen;
use App\Orchid\Screens\Examples\ExampleFieldsScreen;
use App\Orchid\Screens\Examples\ExampleGridScreen;
use App\Orchid\Screens\Examples\ExampleLayoutsScreen;
use App\Orchid\Screens\Examples\ExampleScreen;
use App\Orchid\Screens\Examples\ExampleTextEditorsScreen;
use App\Orchid\Screens\PageListScreen;
use App\Orchid\Screens\PageScreen;
use App\Orchid\Screens\PlatformScreen;
use App\Orchid\Screens\Role\RoleEditScreen;
use App\Orchid\Screens\Role\RoleListScreen;
use App\Orchid\Screens\SettingListScreen;
use App\Orchid\Screens\TemplateListScreen;
use App\Orchid\Screens\TemplateScreen;
use App\Orchid\Screens\User\UserEditScreen;
use App\Orchid\Screens\User\UserListScreen;
use App\Orchid\Screens\User\UserProfileScreen;
use App\Orchid\Screens\FieldListScreen;
use App\Services\MenuService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Tabuna\Breadcrumbs\Trail;

/*
|--------------------------------------------------------------------------
| Dashboard Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the need "dashboard" middleware group. Now create something great!
|
*/

// Main
Route::screen('/main', PlatformScreen::class)
    ->name('platform.main');

// Platform > Profile
Route::screen('profile', UserProfileScreen::class)
    ->name('platform.profile')
    ->breadcrumbs(fn(Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Profile'), route('platform.profile')));

// Platform > System > Users > User
Route::screen('users/{user}/edit', UserEditScreen::class)
    ->name('platform.systems.users.edit')
    ->breadcrumbs(fn(Trail $trail, $user) => $trail
        ->parent('platform.systems.users')
        ->push($user->name, route('platform.systems.users.edit', $user)));

// Platform > System > Users > Create
Route::screen('users/create', UserEditScreen::class)
    ->name('platform.systems.users.create')
    ->breadcrumbs(fn(Trail $trail) => $trail
        ->parent('platform.systems.users')
        ->push(__('Create'), route('platform.systems.users.create')));

// Platform > System > Users
Route::screen('users', UserListScreen::class)
    ->name('platform.systems.users')
    ->breadcrumbs(fn(Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Users'), route('platform.systems.users')));

// Platform > System > Roles > Role
Route::screen('roles/{role}/edit', RoleEditScreen::class)
    ->name('platform.systems.roles.edit')
    ->breadcrumbs(fn(Trail $trail, $role) => $trail
        ->parent('platform.systems.roles')
        ->push($role->name, route('platform.systems.roles.edit', $role)));

// Platform > System > Roles > Create
Route::screen('roles/create', RoleEditScreen::class)
    ->name('platform.systems.roles.create')
    ->breadcrumbs(fn(Trail $trail) => $trail
        ->parent('platform.systems.roles')
        ->push(__('Create'), route('platform.systems.roles.create')));

// Platform > System > Roles
Route::screen('roles', RoleListScreen::class)
    ->name('platform.systems.roles')
    ->breadcrumbs(fn(Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Roles'), route('platform.systems.roles')));

// Группа "Шаблоны"
Route::prefix('templates')->group(function () {
    Route::screen('/list', TemplateListScreen::class)
        ->name('platform.template.list')
        ->breadcrumbs(
            fn(Trail $trail) => $trail
                ->parent('platform.index')
                ->push('Шаблоны', route('platform.template.list'))
        );

    Route::screen('/create', TemplateScreen::class)
        ->name('platform.template.create')
        ->breadcrumbs(
            fn(Trail $trail) => $trail
                ->parent('platform.template.list')
                ->push('Создать')
        );

    Route::screen('/edit/{id}', TemplateScreen::class)
        ->name('platform.template.edit')
        ->breadcrumbs(
            fn(Trail $trail, $id) => $trail
                ->parent('platform.template.list')
                ->push("Редактирование", route('platform.template.edit', $id))
        );
});

// Группа "Страницы"
Route::prefix('pages')->group(function () {
    Route::screen('/list', PageListScreen::class)
        ->name('platform.page.list')
        ->breadcrumbs(
            fn(Trail $trail) => $trail
                ->parent('platform.index')
                ->push('Страницы', route('platform.page.list'))
        );

    Route::screen('/create', PageScreen::class)
        ->name('platform.page.create')
        ->breadcrumbs(
            fn(Trail $trail) => resolve(MenuService::class)
                ->buildAdminPageBreadcrumbs(null, 'create')
                ->reduce(
                    fn($subTrail, $crumb) => $crumb['url']
                        ? $subTrail->push($crumb['title'], $crumb['url'])
                        : $subTrail->push($crumb['title']),
                    $trail->parent('platform.index')
                )
        );

    Route::screen('/edit/{id}', PageScreen::class)
        ->name('platform.page.edit')
        ->breadcrumbs(
            fn(Trail $trail, $id) =>
            resolve(MenuService::class)
                ->buildAdminPageBreadcrumbs(\App\Models\Page::find($id), 'edit')
                ->reduce(
                    fn($subTrail, $crumb) => $crumb['url']
                        ? $subTrail->push($crumb['title'], $crumb['url'])
                        : $subTrail->push($crumb['title']),
                    $trail->parent('platform.index')
                )
        );
});

Route::post('/upload/image', function (Request $request) {
    $path = $request->file('image')->store('images', 'public');
    return response()->json([
        'success' => 1,
        'file' => [
            'url' => Storage::url($path)
        ]
    ]);
})->name('platform.image');

Route::screen('/settings', SettingListScreen::class)
    ->name('platform.settings');

// Управление полями
Route::screen('/fields', FieldListScreen::class)
    ->name('platform.field.list')
    ->breadcrumbs(fn(Trail $trail) => $trail
        ->parent('platform.index')
        ->push('Управление полями', route('platform.field.list')));

// Example...
Route::screen('example', ExampleScreen::class)
    ->name('platform.example')
    ->breadcrumbs(fn(Trail $trail) => $trail
        ->parent('platform.index')
        ->push('Example Screen'));

Route::screen('/examples/form/fields', ExampleFieldsScreen::class)
    ->name('platform.example.fields');
Route::screen('/examples/form/advanced', ExampleFieldsAdvancedScreen::class)
    ->name('platform.example.advanced');
Route::screen('/examples/form/editors', ExampleTextEditorsScreen::class)
    ->name('platform.example.editors');
Route::screen('/examples/form/actions', ExampleActionsScreen::class)
    ->name('platform.example.actions');

Route::screen('/examples/layouts', ExampleLayoutsScreen::class)
    ->name('platform.example.layouts');
Route::screen('/examples/grid', ExampleGridScreen::class)
    ->name('platform.example.grid');
Route::screen('/examples/charts', ExampleChartsScreen::class)
    ->name('platform.example.charts');
Route::screen('/examples/cards', ExampleCardsScreen::class)
    ->name('platform.example.cards');
