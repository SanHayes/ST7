<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Badge;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Image;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Fields\Status;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Http\Requests\NovaRequest;
use App\Nova\Actions\ApplyFillingBorrow;
use App\Nova\Actions\ApplyTurnDownBorrow;

class BorrowOrder extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\BorrowOrder>
     */
    public static $model = \App\Models\BorrowOrder::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
    ];

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
            Text::make(__('AccountNumber'), 'account')->readonly(),
            Number::make(__('lockDividendDays'), 'lock_dividend_days')->displayUsing(function ($value) {
                return $value ? $value . ' 天' : '';
            })->readonly(),
            Number::make(__('BorrowAmount'), 'amount')->step('any')->readonly(),
            Number::make(__('BorrowLixi'), 'day_profit')->step('any')->displayUsing(function ($value) {
                return $value ? $value . ' %' : '';
            })->readonly(),
            Number::make(__('BorrowsumProfit'), 'sum_profit')->step('any')->readonly(),
            Boolean::make(__('isReturn'), 'is_return')->trueValue('1')->falseValue('0')->readonly(),
            DateTime::make(__('OrderTime'),'created_at')->displayUsing(function ($value) {
                return $value ? $value->format('Y-m-d H:i:s') : '';
            })->readonly(),
            DateTime::make(__('subTime'),'sub_time')->displayUsing(function ($value) {
                return $value ? $value->format('Y-m-d H:i:s') : '';
            })->readonly(),
            Badge::make(__('Status'),'status_name')->map([
                '审核中' => 'warning',
                '待还款' => 'info',
                '借贷完成' => 'success',
                '借贷拒绝' => 'danger'
            ]),
            Textarea::make(__('BorrowDesc'), 'borrow_desc')->readonly(),
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
        return [];
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
            (new ApplyFillingBorrow)->showInline(),
            (new ApplyTurnDownBorrow())->showInline(),
        ];
    }
    
    /**
     * Get the displayble label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return __('Borrow Orders');
    }

    /**
     * Get the displayble singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return __('Borrow Orders');
    }
}
