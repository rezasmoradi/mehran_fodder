<?php

use App\Product;
use Hekmatinasser\Verta\Verta;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

if (!function_exists('generate_employee_code')) {
    function generate_employee_code()
    {
        try {
            return random_int(100, 999) . random_int(100, 999);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}

if (!function_exists('generate_payment_number')) {
    function generate_payment_number()
    {
        try {
            return random_int(1111, 9999) . random_int(1111, 9999);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}

if (!function_exists('diff_after_now')) {
    function diff_after_now($timeValue)
    {
        $date = explode('-', substr($timeValue, 0, 10));
        if (substr($timeValue, 11)) {
            $time = explode(':', substr($timeValue, 11));
            if ($time[2]) {
                $received = Verta::createJalali($date[0], $date[1], $date[2], $time[0], $time[1], $time[2])->getTimestamp();
            } else {
                $received = Verta::createJalali($date[0], $date[1], $date[2], $time[0], $time[1])->getTimestamp();
            }
        } else {
            $received = Verta::createJalali($date[0], $date[1], $date[2])->getTimestamp();
        }
        $now = Verta::now()->timezone('Asia/Tehran');
        $timezone = $now->getOffset();
        $diff = $received - ($timezone + $now->getTimestamp());
        return $diff > 0;
    }
}

if (!function_exists('to_georgian')) {
    function to_georgian($timeValue)
    {
        $date = explode('-', substr($timeValue, 0, 10));
        if (substr($timeValue, 11)) {
            $time = explode(':', substr($timeValue, 11));
            $v = Verta::createJalali($date[0], $date[1], $date[2], $time[0], $time[1]);
        } else {
            $v = Verta::createJalali($date[0], $date[1], $date[2]);
        }
        $vTimestamp = $v->datetime()->getTimestamp();
        $now = Verta::now()->timezone('Asia/Tehran');
        $real = $vTimestamp - $now->getOffset();
        $datetime = ((array)Carbon::createFromTimestamp($real)->toDate())['date'];
        return substr($datetime, 0, 19);
    }
}

if (!function_exists('payable')) {
    function payable($productId, $amount)
    {
        $product = Product::query()->where('id', $productId)->first();
        $unitPrice = $product->getAttribute('unit_price');
        $packingWeight = $product->getAttribute('packing_weight');
        $discount = ($amount / $packingWeight) * $product->discount;
        return [floor($discount), floor(($amount / $packingWeight) * ($unitPrice - $product->discount))];
    }
}

if (!function_exists('custom_response')) {

    /**
     * @param Builder $query
     * @param Request $request
     * @return Builder
     */
    function custom_response($query, $request)
    {
        if (!empty($request)) {
            $queries = $request->all();
            foreach ($queries as $key => $value) {
                $q = 'normal';
                $direction = 'asc';
                $op = '=';
                $rawQuery = DB::table('orders AS o')
                    ->selectRaw('o.*, t.order_id, SUM(t.delivery_amount) AS delivered,
                    o.total_amount - SUM(t.delivery_amount) AS remaining, o.total_amount as required, u.*, p.name')
                    ->leftJoin('transportations AS t', 't.order_id', '=', 'o.id')
                    ->leftJoin('users AS u', 'u.id', '=', 'o.user_id')
                    ->leftJoin('products AS p', 'p.id', '=', 'o.product_id')
                    ->groupBy('t.order_id');

                preg_match('~\S+(gt|lt)(_eq)?\d+(_\d{3})?~', $key . $value, $matches);
                if (!empty($matches)) {
                    preg_match('~\W{0}(gt|lt)(_eq)?\D{0}~', $matches[0], $operator);
                    switch ($operator[0]) {
                        case 'gt':
                            $op = '>';
                            break;
                        case 'gt_eq':
                            $op = '>=';
                            break;
                        case 'lt':
                            $op = '<';
                            break;
                        case 'lt_eq':
                            $op = '<=';
                            break;
                        default:
                            $op = '=';
                            break;
                    }
                    $value = substr($value, strlen($operator[0]));
                    if (get_class($request) === 'App\Http\Requests\Payment\GetAllPaymentRequest') {
                        str_replace('_', '.', $value);
                    }
                }

                preg_match('/type!=\w+/', $key, $match);
                if (!empty($match)) {
                    $key = 'type';
                    $value = substr($match[0], 6, strlen($match[0]));
                    $q = 'user_type';
                }

                if (!is_array($value)) {
                    preg_match('~20\d{2}-\d{2}-\d{2}(,\d{2}:\d{2}:\d{2})?_20\d{2}-\d{2}-\d{2}(,\d{2}:\d{2}:\d{2})?~', $value, $matches);
                    preg_match('~20\d{2}-\d{2}-\d{2}(,\d{2}:\d{2}:\d{2})?~', $value, $oneDate);
                    preg_match('~\d+_\d+,\d+_\d+~', $value, $amounts);
                    if (!empty($matches)) {
                        $date = explode('_', $value);
                        if (strpos($date[0], ',') !== false) {
                            $date[0] = str_replace(',', ' ', $date[0]);
                        } elseif (strpos($date[1], ',') !== false) {
                            $date[1] = str_replace(',', ' ', $date[1]);
                        }
                        $value = $date;
                        $q = 'between';
                    } elseif (!empty($oneDate)) {
                        if (strpos($value, ',') !== false) {
                            $value = str_replace(',', ' ', $value);
                            $q = 'time';
                        } elseif ($key !== 'sum_payments') {
                            $q = 'date';
                        }
                    } elseif (!empty($amounts)) {
                        $values = explode(',', $amounts[0]);
                        $values[0] = str_replace('_', '.', $values[0]);
                        $values[1] = str_replace('_', '.', $values[1]);
                        foreach ($values as $index => $item) {
                            $val = str_replace('_', '.', $item);
                            $values[$index] = $val;
                        }
                        $value = $values;
                        $q = 'between';
                    }
                }

                switch ($key) {
                    case 'deleted_at':
                        if ($value === '0') $value = null;
                        elseif ($value === '1') {
                            $op = '<>';
                            $value = null;
                        } else  $q = 'date';
                        break;
                    case 'full_pay':
                        if ($value === '1') {
                            $q = 'join_on_pay_full_pay';
                        } else {
                            $q = 'join_on_pay_half_pay';
                        }
                        break;
                    case 'transport_type':
                        $q = 'join_on_transport_type';
                        break;
                    case 'completed':
                        $q = $value === '1' ? 'join_on_transport_complete' : 'join_on_transport_not_complete';
                        break;
                    case 'pay_type':
                        $q = 'join_on_pay_type';
                        break;
                    case 'orderBy':
                        preg_match('~\w+_dir_\w+~', $value, $matches);
                        if (!empty($matches)) {
                            preg_match('~\w_(asc|desc)~', $matches[0], $dir);
                            $direction = $dir[1];
                            preg_match('~\w[^_]~', $value, $val);
                            $value = $val[0];
                        }
                        $class = 'App\Http\Requests\Transportation\GetAllTransportationsRequest';
                        if ($value === 'id' && get_class($request) === $class) {
                            $q = 'order_by_transportations_id';
                        } else {
                            $q = 'order_by';
                        }
                        break;
                    case 'debts':
                        $q = $value === '0' ? 'debit' : 'credit';
                        break;
                    case 'sum_payments':
                        $key = 'created_at';
                        if (!is_array($value)) {
                            if (strpos($value, '_') !== false) {
                                $value = explode('_', $value);
                                $q = 'sum_payments_in_multiple_days';
                            } else {
                                $q = 'sum_payments_in_one_day';
                            }
                        }
                        break;
                    case 'person_debts':
                        $q = 'person_debts';
                        break;
                    case 'year_financial_report':
                        $q = 'year_financial_report';
                        break;
                    case 'published':
                        $q = 'published_events';
                        break;
                    case 'user_id':
                        $q = 'user_transportations';
                        break;
                }

                switch ($q) {
                    case 'date':
                        $query = $query->whereDate($key, $op, $value);
                        break;
                    case 'join_on_pay_full_pay':
                        $query = $query
                            ->selectRaw('SUM(payments.payed_amount) AS pays,
                                orders.payable_amount AS payable, COUNT(payments.payed_amount) AS pay_count, orders.*')
                            ->leftJoin('payments', 'orders.id', '=', 'payments.order_id')
                            ->groupBy(DB::raw('payments.order_id'))
                            ->havingRaw('pays >= payable');
                        break;
                    case 'join_on_pay_half_pay':
                        $query = $query
                            ->selectRaw('SUM(payments.payed_amount) AS pays,
                               orders.payable_amount AS payable, COUNT(payments.payed_amount) AS pay_count, orders.*')
                            ->leftJoin('payments', 'orders.id', '=', 'payments.order_id')
                            ->groupBy(DB::raw('payments.order_id'))
                            ->havingRaw('pays < payable');
                        break;
                    case 'join_on_transport_type':
                        $query = $query
                            ->leftJoin('orders', 'orders.id', '=', 'transportations.order_id')
                            ->where('orders.type', $value);
                        break;
                    case 'join_on_transport_complete':
                        $query = $rawQuery->havingRaw('delivered >= o.total_amount');
                        break;
                    case 'join_on_transport_not_complete':
                        $query = $rawQuery->havingRaw('delivered < o.total_amount');
                        break;
                    case 'join_on_pay_type':
                        $query = $query
                            ->join('orders', 'payments.order_id', '=', 'orders.id')
                            ->where('orders.type', $value);
                        break;
                    case 'order_by_transportations_id':
                        $query = $query->orderBy('transportations.id', $direction);
                        break;
                    case 'order_by':
                        $query = $query->orderBy($value, $direction);
                        break;
                    case 'between':
                        $query = $query->whereBetween($key, $value);
                        break;
                    case 'time':
                        $query = $query->whereTime($key, $op, $value);
                        break;
                    case 'debit':
                        $query = $query
                            ->selectRaw('*, SUM(payments.payed_amount) as payed, (o.payable_amount) as payable,
                            SUM(payed_amount) - o.payable_amount as debit')
                            ->leftJoin('orders as o', 'o.id', '=', 'payments.order_id')
                            ->groupBy('order_id')->having('payed', '>', 'payable');
                        break;
                    case 'credit':
                        $query = $query
                            ->selectRaw('*, SUM(payments.payed_amount) as payed, (o.payable_amount) as payable,
                             SUM(payed_amount) - o.payable_amount as credit')
                            ->leftJoin('orders as o', 'o.id', '=', 'payments.order_id')
                            ->groupBy('order_id')->having('payed', '<', 'payable');
                        break;
                    case 'sum_payments_in_multiple_days':
                        $query = $query
                            ->selectRaw('SUM(payed_amount) as total_payments')
                            ->whereBetween($key, $value);
                        $key = 'sum_payments';
                        break;
                    case 'sum_payments_in_one_day':
                        $query = $query
                            ->selectRaw('COUNT(payments.payed_amount) AS count, SUM(payments.payed_amount) AS total_payment')
                            ->whereDate($key, '=', $value);
                        $key = 'sum_payments';
                        break;
                    case 'person_debts':
                        $query = $query->selectRaw('SUM(payments.payed_amount) AS pays,
                           o.payable_amount AS payable, COUNT(payments.payed_amount) AS pay_count,
                           (o.payable_amount - SUM(payments.payed_amount)) AS debit, o.*, u.*')
                            ->leftJoin('orders AS o', 'o.id', '=', 'payments.order_id')
                            ->leftJoin('users AS u', 'o.user_id', '=', 'u.id')
                            ->where('o.user_id', '=', $value)
                            ->groupBy('payments.order_id')
                            ->having('payable', '>', 'pays');
                        break;
                    case 'year_financial_report':
                        $unionOnSale = DB::table('payments')
                            ->selectRaw('COUNT(*) as pay_count, YEAR(o.created_at), SUM(payments.payed_amount) as pays, o.type, SUM(o.total_amount) as sale')
                            ->leftJoin('orders AS o', 'o.id', '=', 'payments.order_id')
                            ->where('payments.status', '=', 1)
                            ->whereYear('o.created_at', $value)
                            ->groupBy('o.type');
                        /*$query = $query->selectRaw('COUNT(*), SUM(orders.total_amount) as sale, orders.*')
                            ->leftJoin('products AS p', 'orders.product_id', '=', 'p.id')
                            ->whereYear('orders.created_At', '=', $value)
                            ->groupBy('p.id')
                            ->having('orders.type', '=', 'sale')
                            ->union($unionOnPurchase);*/
                        /*$query = DB::select(
                            DB::raw("SELECT COUNT(*), orders.*, SUM(orders.total_amount) as sale FROM orders
                                            LEFT JOIN products p on orders.product_id = p.id
                                            WHERE orders.type = 'sale'
                                            GROUP BY p.id
                                            HAVING orders.type = 'sale'
                                            UNION
                                            SELECT COUNT(*), orders.*, SUM(orders.total_amount) as sale FROM orders
                                            LEFT JOIN products p on orders.product_id = p.id
                                            WHERE orders.type = 'purchase'
                                            GROUP BY p.id
                                            HAVING orders.type = 'purchase'"
                            ));*/
                        $query = DB::table('payments')
                            ->selectRaw('COUNT(*) as pay_count, YEAR(o.created_at), SUM(payments.payed_amount) as pays, o.type, SUM(o.total_amount) as purchase')
                            ->leftJoin('orders AS o', 'o.id', '=', 'payments.order_id')
                            ->where('payments.status', '=', 1)
                            ->whereYear('o.created_at', $value)
                            ->groupBy('o.type')
                            ->union($unionOnSale);
                        break;
                    case 'published_events':
                        if ($value === '0')
                            $query = $query->where('publish_date', '>', now()->toDateTime());
                        else
                            $query = $query->where('publish_date', '<=', now()->toDateTime())
                                ->orWhere('publish_date', '=', null);
                        break;
                    case 'user_type':
                        $query = $query->where($key, '<>', $value);
                        break;
                    case 'user_transportations':
                        $query = $query
                        ->join('orders', 'orders.id', '=', 'transportations.order_id')
                        ->where('user_id', $value);
                        break;

                    default:
                        $query = $query->where($key, $op, $value);
                        break;
                }
                unset($key);
            }
            return $query;
        } else {
            return $query;
        }
    }
}


if (!function_exists('client_ip')) {
    function client_ip()
    {
        return $_SERVER['REMOTE_ADDR'] . '-' . md5($_SERVER['HTTP_USER_AGENT']);
    }
}

if (!function_exists('to_jalali')) {
    function to_jalali(array $georgianDates)
    {
        $jalaliDates = [];
        $hasSecond = ['created_at', 'updated_at', 'deleted_at'];
        foreach ($georgianDates as $key => $date) {
            $jalaliDates[$key] = is_null($date) ? null : Verta::instance($date)->timezone('Asia/Tehran');
            if (!is_null($jalaliDates[$key])) {
                if (array_search($key, $hasSecond)) {
                    $jalaliDates[$key]->format('Y-m-d H:i:s');
                } else {
                    $jalaliDates[$key]->format('Y-m-d H:i');
                }
            } else {
                $jalaliDates[$key] = null;
            }
        }

        return $jalaliDates;
    }
}
