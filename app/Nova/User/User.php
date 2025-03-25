<?php

namespace App\Nova\User;

use Acme\Analytics\Analytics;
use App\Nova\Actions\UserRecharge;
use App\Nova\Resource;
use Illuminate\Validation\Rules;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Password;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use KirschbaumDevelopment\Nova\InlineSelect;
use Laravel\Nova\Http\Requests\NovaRequest;

class User extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Users>
     */
    public static $model = \App\Models\Users::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
         'email','ID'
    ];

    /**
     * Whether to show borders for each column on the X-axis.
     *
     * @var bool
     */
    public static $showColumnBorders = false;

    /**
     * The visual style used for the table. Available options are 'tight' and 'default'.
     *
     * @var string
     */
    public static $tableStyle = 'tight';

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static function group(){
        return __('User');
    }

    /**
     * Custom priority level of the resource.
     *
     * @var int
     */
    public static $priority = 9999;

    public static function indexQuery(NovaRequest $request, $query)
    {
        return $query->where('simulation', 0);

    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        
        return [
            ID::make()->sortable(),

            Text::make(__('Email'), 'email')
                ->sortable()
                ->rules('email', 'max:254')
                ->creationRules('required', 'unique:users,email')
                ->updateRules('unique:users,email,{{resourceId}}'),

            Text::make(__('Superior'),'superior')->readonly(),

            Text::make(__('ExtensionCode'), 'extension_code'),

            Text::make(__('UserType'),'my_agent_level')->readonly(),

            Text::make(__('UserLevel'),'level')->readonly(),

            # Select字段/枚举字段
            InlineSelect::make(__('Status'), 'status')
                ->options([
                    0 => '正常',
                    1 => '冻结',
                ])
                ->displayUsingLabels()
                ->inlineOnIndex()
                ->enableOneStepOnIndex(),

            Text::make(__('Balance'),'usdt')->readonly(),
            
            Password::make(__('Password'), 'password')
                ->onlyOnForms()
                ->creationRules('required', Rules\Password::defaults())
                ->updateRules('nullable', Rules\Password::defaults()),

            // 添加备注字段
            Text::make(__('Label'), 'label')
                ->sortable()
                ->rules('nullable', 'max:30')
                ->hideFromIndex(), # 可以选择隐藏于索引视图中

            DateTime::make(__('Created At'),'created_at')->readonly(),	# 只读字段

            DateTime::make(__('Last Login Time'),'last_login_time')->readonly(),	# 只读字段
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function cards(NovaRequest $request)
    {
        return [
        ];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function filters(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function lenses(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function actions(NovaRequest $request)
    {
        return [
            (new UserRecharge())->showInline(),
        ];
    }

    /**
     * Get the displayble label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return __('Users');
    }

    /**
     * Get the displayble singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return __('Users');
    }

    /**
     * Get the value that should be displayed to represent the resource.
     *
     * @return string
     */
    public function title()
    {
        return $this->name;
    }

    /**
     * Get the search result subtitle for the resource.
     *
     * @return string
     */
    public function subtitle()
    {
        return $this->name . '/' . $this->email;
    }
}
