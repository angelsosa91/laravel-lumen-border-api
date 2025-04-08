<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\BorderController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\EventController;
use App\Http\Controllers\API\GenderController;
use App\Http\Controllers\API\SexController;
use App\Http\Controllers\API\ModulesController;
use App\Http\Controllers\API\PrivilegesController;
use App\Http\Controllers\API\CountryController;
use App\Http\Controllers\API\AlertController;
use App\Http\Controllers\API\MigrationsController;
use App\Http\Controllers\API\ReportingController;
use App\Http\Controllers\API\RolController;
use App\Http\Controllers\API\SuspectController;
use App\Http\Controllers\API\InterpolController;
use App\Http\Controllers\API\DocumentController;
use App\Http\Controllers\API\CasesController;
use App\Http\Controllers\API\InfoController;
use App\Http\Controllers\API\DestinationController;
use App\Http\Controllers\API\EntryController;
use App\Http\Controllers\API\RegionController;
use App\Http\Controllers\API\AssisHostController;
use App\Http\Controllers\API\AssisTransController;
use App\Http\Controllers\API\AssisSubController;
use App\Http\Controllers\API\AssisPTMController;
use App\Http\Controllers\API\AssisKitController;
use App\Http\Controllers\API\AssisFilesController;
use App\Http\Controllers\API\AssisDerivationController;
use App\Http\Controllers\API\TypesController;
use App\Http\Controllers\API\LogController;
use App\Http\Controllers\API\FamilyController;
use App\Http\Controllers\API\BankController;
use App\Http\Controllers\API\VulnerabilityScaleController;
use App\Http\Controllers\API\VulnerabilityScaleTypeController;
use App\Http\Controllers\API\VulnerabilityScaleRangeController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::post('login', [AuthController::class, 'login'])->middleware("throttle:5,2");

Route::middleware('auth:api')->group( function () {
    //token auth
    //Route::post('register', [AuthController::class, 'register']);
    Route::get('logout', [AuthController::class, 'logout']);
    Route::get('validate', [UserController::class, 'validateToken']);
    //users
    Route::post('users', [UserController::class, 'show']);
    Route::post('usersByRole', [UserController::class, 'showByRole']);
    Route::put('user/{id}', [UserController::class, 'update']);
    Route::delete('user/{id}', [UserController::class, 'delete']);
    Route::get('profile', [UserController::class, 'profile']);
    Route::get('privileges/{module}', [UserController::class, 'privileges']);
    //Route::post('user', [UserController::class, 'create']);

    //migrations
    Route::post('print', [MigrationsController::class, 'printOne']);
	Route::post('printMov', [MigrationsController::class, 'printTwo']);
	Route::get('docs/{id}', [MigrationsController::class, 'docs']);
	Route::post('movements', [MigrationsController::class, 'data']);
    Route::post('movement', [MigrationsController::class, 'create']);
    //Route::put('movement/{id}', [MigrationsController::class, 'update']);
    //Route::delete('movement/{id}', [MigrationsController::class, 'delete']);

    //roles
    Route::post('rolAll', [RolController::class, 'all']);
    Route::post('roles', [RolController::class, 'show']);
    Route::post('role', [RolController::class, 'create']);
    Route::put('role/{id}', [RolController::class, 'update']);
    Route::delete('role/{id}', [RolController::class, 'delete']);

    //privileges
    Route::post('privileges/{id}', [PrivilegesController::class, 'show']);
    Route::post('privilege', [PrivilegesController::class, 'create']);
    Route::put('privilege/{id}', [PrivilegesController::class, 'update']);
    Route::delete('privilege/{id}', [PrivilegesController::class, 'delete']);

    //suspects
    Route::post('alerts', [SuspectController::class, 'show']);
    Route::post('alert', [SuspectController::class, 'create']);
    Route::put('alert/{id}', [SuspectController::class, 'update']);
    Route::delete('alert/{id}', [SuspectController::class, 'delete']);

    //documents
    Route::post('doctype', [DocumentController::class, 'all']);
    Route::get('types', [DocumentController::class, 'all']);
    Route::post('types', [DocumentController::class, 'show']);
    Route::post('type', [DocumentController::class, 'create']);
    Route::put('type/{id}', [DocumentController::class, 'update']);
    Route::delete('type/{id}', [DocumentController::class, 'delete']);

    //border
    Route::get('borderAll', [BorderController::class, 'all']);
    Route::post('borders', [BorderController::class, 'show']);
    Route::post('border', [BorderController::class, 'create']);
    Route::put('border/{id}', [BorderController::class, 'update']);
    Route::delete('border/{id}', [BorderController::class, 'delete']);
    Route::get('borderAllBySync', [BorderController::class, 'allBySync']);

    //destination
    Route::get('destination', [DestinationController::class, 'all']);
    Route::post('destinations', [DestinationController::class, 'show']);
    Route::post('destination', [DestinationController::class, 'create']);
    Route::put('destination/{id}', [DestinationController::class, 'update']);
    Route::delete('destination/{id}', [DestinationController::class, 'delete']);

    //reporting
    Route::get('movByDocument', [ReportingController::class, 'showByDocument']);
    Route::get('movByCountry', [ReportingController::class, 'showByCountry']);
    Route::get('movByBorder', [ReportingController::class, 'showByBorder']);
    Route::get('movByDestination', [ReportingController::class, 'showByDestination']);
    Route::get('movByReason', [ReportingController::class, 'showByReason']);
    Route::post('borderSync/{name}', [ReportingController::class, 'showLastSyncByBorder']);

    //country
    Route::get('country', [CountryController::class, 'all']);
    Route::post('countryAll', [CountryController::class, 'all']);
    Route::post('countries', [CountryController::class, 'show']);
    Route::post('country', [CountryController::class, 'create']);
    Route::put('country/{id}', [CountryController::class, 'update']);
    Route::delete('country/{id}', [CountryController::class, 'delete']);

    //region
    Route::post('regionAll', [RegionController::class, 'all']);
    Route::get('region', [RegionController::class, 'all']);
    Route::post('regions', [RegionController::class, 'show']);
    Route::post('region', [RegionController::class, 'create']);
    Route::put('region/{id}', [RegionController::class, 'update']);
    Route::delete('region/{id}', [RegionController::class, 'delete']);

    //alerts by movement
    Route::get('movAlerts', [AlertController::class, 'show']);
    Route::put('movAlerts/{id}', [AlertController::class, 'update']);
    Route::get('movAlertsGroup', [AlertController::class, 'countByGroup']);
    Route::get('movAlertsByQtyIn', [AlertController::class, 'countByIn']);
    Route::get('movAlertsByQtyOut', [AlertController::class, 'countByOut']);

    //alerts by movement
    Route::get('health/{id}', [InfoController::class, 'show']);
    Route::get('healthByCondition/{id}', [InfoController::class, 'showByCondition']);
    Route::get('healthByDisease/{id}', [InfoController::class, 'showByDisease']);
    Route::get('healthByTravel/{id}', [InfoController::class, 'showByTravel']);
    Route::get('healthByService/{id}', [InfoController::class, 'showByService']);
    Route::get('healthByEducation/{id}', [InfoController::class, 'showByEducation']);
    Route::get('healthByPregnancy/{id}', [InfoController::class, 'showByPregnancy']);
    Route::get('healthBySecurity/{id}', [InfoController::class, 'showBySecurity']);
    Route::get('healthByNetwork/{id}', [InfoController::class, 'showByNetwork']);

    //cases
    Route::post('cases', [CasesController::class, 'show']);
    Route::get('cases/{id}', [CasesController::class, 'showById']);
    Route::get('cases/{id}/face', [CasesController::class, 'viewFaceCam']);
    Route::put('cases/{id}/assis/{op}', [CasesController::class, 'createById']);
    Route::put('cases/{id}', [CasesController::class, 'closeById']);
    Route::post('cases/{id}', [CasesController::class, 'showAssisByCaseId']);
    Route::delete('cases/{id}/assis', [CasesController::class, 'deleteAssisByCaseId']);
    Route::post('casesStatus/{id}', [CasesController::class, 'showCasesStatusById']);
    Route::post('cases/{id}/file', [CasesController::class, 'uploadFile']);
    Route::get('cases/{id}/file', [CasesController::class, 'viewFile']);

    //assis
    Route::post('assis', [CasesController::class, 'showAllAssis']);
    Route::get('assis/{id}', [CasesController::class, 'showAllAssisById']);
    Route::delete('assis/{id}', [CasesController::class, 'deleteAssisById']);
    Route::put('assis/{id}', [CasesController::class, 'closeAssisById']);
    Route::post('assisStatus/{id}', [CasesController::class, 'showAssisStatusById']);
    Route::get('assisById/{id}', [CasesController::class, 'showOneAssisById']);

    //entry
    Route::post('entry', [EntryController::class, 'show']);
    Route::get('entry/{id}', [EntryController::class, 'showById']);
    Route::get('entry/{id}/face', [EntryController::class, 'viewFaceCam']);
    Route::put('entry/{id}', [EntryController::class, 'updateById']);
    Route::put('entryById/{id}', [EntryController::class, 'updateEntryById']);
    Route::post('entryStatus/{id}', [EntryController::class, 'showEntryStatusById']);

    //modules
    Route::post('modules', [ModulesController::class, 'show']);
    //gender
    Route::post('gender', [GenderController::class, 'show']);
    Route::get('status', [GenderController::class, 'showStatus']);
    //sex
    Route::post('sex', [SexController::class, 'show']);
    Route::get('sex', [SexController::class, 'show']);
    //events
    Route::post('events', [EventController::class, 'show']);
    //interpol
    Route::get('interpol/{dni}/{country}', [InterpolController::class, 'show']);

    //assistance host
    Route::post('hostAll', [AssisHostController::class, 'show']);
    Route::post('hostById/{id}', [AssisHostController::class, 'showById']);
    Route::post('host/{id}', [AssisHostController::class, 'createById']);
    Route::put('host/{id}', [AssisHostController::class, 'updateById']);
    //Route::delete('host/{id}', [AssisHostController::class, 'deleteById']);

    //assistance trans
    Route::post('transAll', [AssisTransController::class, 'show']);
    Route::post('transById/{id}', [AssisTransController::class, 'showById']);
    Route::post('trans/{id}', [AssisTransController::class, 'createById']);
    Route::put('trans/{id}', [AssisTransController::class, 'updateById']);
    //Route::delete('trans/{id}', [AssisTransController::class, 'deleteById']);

    //assistance sub
    Route::post('subAll', [AssisSubController::class, 'show']);
    Route::post('subById/{id}', [AssisSubController::class, 'showById']);
    Route::post('sub/{id}', [AssisSubController::class, 'createById']);
    Route::put('sub/{id}', [AssisSubController::class, 'updateById']);
    //Route::delete('sub/{id}', [AssisSubController::class, 'deleteById']);

    //assistance ptm
    Route::post('ptmAll', [AssisPTMController::class, 'show']);
    Route::post('ptmById/{id}', [AssisPTMController::class, 'showById']);
    Route::post('ptm/{id}', [AssisPTMController::class, 'createById']);
    Route::put('ptm/{id}', [AssisPTMController::class, 'updateById']);
    //Route::delete('ptm/{id}', [AssisPTMController::class, 'deleteById']);

    //assistance kit
    Route::post('kitAll', [AssisKitController::class, 'show']);
    Route::post('kitById/{id}', [AssisKitController::class, 'showById']);
    Route::post('kit/{id}', [AssisKitController::class, 'createById']);
    Route::put('kit/{id}', [AssisKitController::class, 'updateById']);
    //Route::delete('kit/{id}', [AssisKitController::class, 'deleteById']);

    //assistance derivation
    Route::post('deriAll', [AssisDerivationController::class, 'show']);
    Route::post('deriById/{id}', [AssisDerivationController::class, 'showById']);
    Route::post('deri/{id}', [AssisDerivationController::class, 'createById']);
    Route::put('deri/{id}', [AssisDerivationController::class, 'updateById']);
    //Route::delete('deri/{id}', [AssisDerivationController::class, 'deleteById']);

    //host extension
    Route::post('hostExtById/{id}', [AssisHostController::class, 'showExtensionById']);
    Route::post('hostExt/{id}', [AssisHostController::class, 'createExtensionById']);
    Route::put('hostExt/{id}', [AssisHostController::class, 'updateExtensionById']);
    Route::delete('hostExt/{id}', [AssisHostController::class, 'deleteExtensionById']);

    //trans extension
    Route::post('transExtById/{id}', [AssisTransController::class, 'showExtensionById']);
    Route::post('transExt/{id}', [AssisTransController::class, 'createExtensionById']);
    Route::put('transExt/{id}', [AssisTransController::class, 'updateExtensionById']);
    Route::delete('transExt/{id}', [AssisTransController::class, 'deleteExtensionById']);

    //assis files
    Route::post('filesBy/{id}', [AssisFilesController::class, 'showById']);
    Route::post('files/{id}', [AssisFilesController::class, 'uploadFile']);
    Route::get('files/{id}', [AssisFilesController::class, 'viewFile']);
    Route::delete('files/{id}', [AssisFilesController::class, 'removeFileById']);

    //types
    Route::post('diagnosisBy', [TypesController::class, 'showDiagnosis']);
    Route::post('potentialBy', [TypesController::class, 'showPotential']);
    Route::post('folioBy', [TypesController::class, 'showFolio']);
    Route::post('diagnosis', [TypesController::class, 'createDiagnosis']);
    Route::post('potential', [TypesController::class, 'createPotential']);
    Route::post('folio', [TypesController::class, 'createFolio']);
    Route::put('diagnosis/{id}', [TypesController::class, 'updateDiagnosis']);
    Route::put('potential/{id}', [TypesController::class, 'updatePotential']);
    Route::put('folio/{id}', [TypesController::class, 'updateFolio']);
    Route::get('diagnosis', [TypesController::class, 'showAllDiagnosis']);
    Route::get('potential', [TypesController::class, 'showAllPotential']);
    Route::get('folio', [TypesController::class, 'showAllFolio']);

    //log
    Route::post('log/{uuid}', [LogController::class, 'create']);

    //vulnerability scale
    Route::get('vulnerability', [VulnerabilityScaleController::class, 'all']);
    Route::get('vulnerabilityByType/{id}', [VulnerabilityScaleController::class, 'allByType']);
    Route::post('vulnerabilities', [VulnerabilityScaleController::class, 'show']);
    Route::post('vulnerability', [VulnerabilityScaleController::class, 'create']);
    Route::put('vulnerability/{id}', [VulnerabilityScaleController::class, 'update']);
    //Route::delete('vulnerability/{id}', [VulnerabilityScaleController::class, 'delete']);

    //vulnerability scale type
    Route::get('vulnerabilityType', [VulnerabilityScaleTypeController::class, 'all']);
    Route::post('vulnerabilityTypeAll', [VulnerabilityScaleTypeController::class, 'all']);
    Route::post('vulnerabilityTypes', [VulnerabilityScaleTypeController::class, 'show']);
    Route::post('vulnerabilityType', [VulnerabilityScaleTypeController::class, 'create']);
    Route::put('vulnerabilityType/{id}', [VulnerabilityScaleTypeController::class, 'update']);
    //Route::delete('vulnerabilityType/{id}', [VulnerabilityScaleController::class, 'delete']);

    //vulnerability scale range
    Route::get('vulnerabilityRange', [VulnerabilityScaleRangeController::class, 'all']);
    Route::post('vulnerabilityRanges', [VulnerabilityScaleRangeController::class, 'show']);
    Route::post('vulnerabilityRange', [VulnerabilityScaleRangeController::class, 'create']);
    Route::put('vulnerabilityRange/{id}', [VulnerabilityScaleRangeController::class, 'update']);
    //Route::delete('vulnerabilityRange/{id}', [VulnerabilityScaleController::class, 'delete']);

    //family
    Route::post('familyById/{id}', [FamilyController::class, 'showById']);
    Route::post('family/{id}', [FamilyController::class, 'create']);
    Route::put('family/{id}', [FamilyController::class, 'update']);
    Route::delete('family/{id}', [FamilyController::class, 'delete']);

    //bank
    Route::post('bankById/{id}', [BankController::class, 'showById']);
    Route::post('bank/{id}', [BankController::class, 'create']);
    Route::put('bank/{id}', [BankController::class, 'update']);
    Route::delete('bank/{id}', [BankController::class, 'delete']);
});
/*
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
*/
