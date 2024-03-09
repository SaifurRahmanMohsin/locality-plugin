<?php namespace Mohsin\Locality;

use Backend;
use System\Classes\PluginBase;
use RainLab\Location\Models\State;
use RainLab\Location\Controllers\Locations as LocationController;

/**
 * Locality Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * @var array Plugin dependencies
     */
    public $require = ['RainLab.Location'];

    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'mohsin.locality::lang.plugin.name',
            'description' => 'mohsin.locality::lang.plugin.description',
            'author'      => 'Saifur Rahman Mohsin',
            'icon'        => 'icon-building'
        ];
    }

    /**
     * Boot method, called right before the request route.
     *
     * @return void
     */
    public function boot()
    {
        State::extend(function ($model) {
            $model->hasMany['localities'] = ['Mohsin\Locality\Models\Locality'];
        });

        LocationController::extendFormFields(function ($form, $model, $context) {
            if (!$model instanceof State) {
                return;
            }

            $form->addFields([
                'localities' => [
                    'label' => 'mohsin.locality::lang.plugin.name',
                    'type'  => 'partial',
                    'path'  => 'plugins/mohsin/locality/models/locality/views/field_localities'
                ],
            ]);
        });

        LocationController::extend(function ($controller) {
            $controller->addDynamicProperty('localitiesFormWidget', null);

            $controller->addDynamicMethod('onLoadCreateLocalityForm', function () use ($controller) {
                $controller->vars['localitiesFormWidget'] = $controller->localitiesFormWidget;
        
                $controller->vars['stateId'] = post('manage_id');
        
                return $controller->makePartial('locality_create_form');
            });

            $controller->addDynamicMethod('onCreateLocality', function () use ($controller) {
                $data = $controller->localitiesFormWidget->getSaveData();
        
                $model = new \Mohsin\Locality\Models\Locality;
        
                $model->fill($data);

                $state = $controller->getStateModel();
                
                $model->state = $state;
                
                $model->save();

                $state->localities()->add($model, $controller->localitiesFormWidget->getSessionKey());
        
                return $controller->refreshStateLocalitiesList();
            });

            $controller->addDynamicMethod('onDeleteLocality', function () use ($controller) {
                $localityId = post('locality_id');
        
                $model = \Mohsin\Locality\Models\Locality::find($localityId);
        
                $state = $controller->getStateModel();
        
                $state->localities()->remove($model, $controller->localitiesFormWidget->getSessionKey());
        
                $model->delete();
        
                return $controller->refreshStateLocalitiesList();
            });

            $controller->addDynamicMethod('onToggleLocalityEnabled', function () use ($controller) {
                $localityId = post('locality_id');
                $enabled    = post('enabled');
        
                $model = \Mohsin\Locality\Models\Locality::find($localityId);
                $model->is_enabled = $enabled ? 0 : 1;
                $model->save();
        
                return $controller->refreshStateLocalitiesList();
            });

            $controller->addDynamicMethod('refreshStateLocalitiesList', function () use ($controller) {
                $localities = $controller->getStateModel()
                    ->localities()
                    ->withDeferred($controller->localitiesFormWidget->getSessionKey())
                    ->get()
                ;
        
                $controller->vars['localities'] = $localities;
        
                return ['#localityList' => $controller->makePartial('localities_list')];
            });

            $controller->addDynamicMethod('getStateModel', function () {
                $stateId = post('manage_id');
        
                $state = $stateId
                    ? \RainLab\Location\Models\State::find($stateId)
                    : new \RainLab\Location\Models\State;
        
                return $state;
            });

            $controller->addDynamicMethod('createStateLocalityFormWidget', function () use ($controller) {
                $config = $controller->makeConfig('$/mohsin/locality/models/locality/fields.yaml');
        
                $config->alias = 'localityForm';
        
                $config->arrayName = 'Locality';
        
                $config->model = new \Mohsin\Locality\Models\Locality;
        
                $widget = $controller->makeWidget('Backend\Widgets\Form', $config);
        
                $widget->bindToController();
        
                return $widget;
            });

            $controller->bindEvent('page.beforeDisplay', function ($action, $params) use ($controller) {
                $controller->localitiesFormWidget = $controller->createStateLocalityFormWidget();
                $controller->addViewPath('plugins/mohsin/locality/models/locality/views');
            });
        });
    }
}
