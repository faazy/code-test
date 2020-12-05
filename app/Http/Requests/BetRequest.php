<?php

namespace App\Http\Requests;

use App\Models\Player;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class BetRequest extends FormRequest
{
    const SELECTIONS_MIN = 1;
    const SELECTIONS_MAX = 20;

    const SELECTION_ODDS_MIN = 1;
    const SELECTION_ODDS_MAX = 10_000;

    const MAX_WIN_AMOUNT = 20_000;

    const STAKE_AMOUNT_MIN = 0.3;
    const STAKE_AMOUNT_MAX = 10_000;

    public const EXCEPTION_CODES = [
        'unknown' => ['code' => 0, 'message' => 'Unknown error'],
        'mismatch' => ['code' => 1, 'message' => 'Betslip structure mismatch'],
        'stake_amount_min' => ['code' => 2, 'message' => 'Minimum stake amount is %s'],
        'stake_amount_max' => ['code' => 3, 'message' => 'Minimum stake amount is %s'],
        'selections_min' => ['code' => 4, 'message' => 'Minimum number of selections is %s'],
        'selections_max' => ['code' => 5, 'message' => 'Maximum number of selections is %s'],
        'selection_odds_min' => ['code' => 6, 'message' => 'Minimum odds are %s'],
        'selection_odds_max' => ['code' => 7, 'message' => 'Maximum odds are %s'],
        'selection_distinct' => ['code' => 8, 'message' => 'Duplicate selection found'],
        'max_win_amount' => ['code' => 9, 'message' => 'Maximum win amount is %s'],
        'previous_action' => ['code' => 10, 'message' => 'Your previous action is not finished yet'],
        'balance_insufficient' => ['code' => 11, 'message' => 'Insufficient balance']
    ];

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'player_id' => 'required',
            'stake_amount' => 'required|numeric|min:0.3|max:10000',
            'selections' => 'required|array|min:1|max:20',
            'selections.*.id' => 'required|distinct',
            'selections.*.odds' => 'numeric|min:1|max:10000'
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param Validator $validator
     * @return void
     */
    public function withValidator(Validator $validator)
    {
        $validator->after(function (\Illuminate\Validation\Validator $validator) {
            if (!$this->isWinAmountValid()) {
                $validator->errors()->add('max_win_amount', 'max_win_amount');
            }

            $player = Player::find(request()->player_id);

            if (($player && !$player->isBalanceSufficient(request()->stake_amount))
                || request()->stake_amount > Player::DEFAULT_BALANCE)
                $validator->errors()->add('balance_insufficient', 'balance_insufficient');
        });
    }


    /**
     * Validate the given request with the given rules.
     *
     * @param Validator $validator
     * @return void
     */
    protected function failedValidation(Validator $validator)
    {
        $errors = $this->transformErrors($validator);

        throw new HttpResponseException(response()->json($errors, Response::HTTP_UNPROCESSABLE_ENTITY));
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'stake_amount.max' => 'stake_amount_max',
            'stake_amount.min' => 'stake_amount_min',
            'selections.min' => 'selections_min',
            'selections.max' => 'selections_max',
            'selections.*.id.distinct' => 'selection_distinct',
            'selections.*.odds.min' => 'selection_odds_min',
            'selections.*.odds.max' => 'selection_odds_max',
        ];
    }

    /**
     * Create Custom Error response structure
     *
     * @param Validator $validator
     * @return array[]
     */
    protected function transformErrors(Validator $validator)
    {
        $global_errors = [];
        $selections_errors = [];

        foreach ($validator->errors()->getMessages() as $field => $message) {
            $message = last($message);
            $key = $this->getConst(strtoupper($message));

            if (strpos($field, 'selections') === false) {
                $global_errors[] = $this->getErrorMessage($message, $key);
            } else {

                if (preg_match('~^selections.\K.*?(?=.id$)~', $field, $index)) {
                    $selections_errors[] = [
                        'id' => $this->request->get("selections")[$index[0]]['id'],
                        'errors' => [$this->getErrorMessage($message, $key)]
                    ];
                } else if ($message == 'selection_odds_min' || $message == 'selection_odds_max') {
                    $index = explode('.', $field);
                    $id = $this->request->get("selections")[$index[1]]['id'];
                    $selections_errors[] = [
                        'id' => $id,
                        'errors' => [$this->getErrorMessage($message, $key)]
                    ];
                }

            }
        }

        return ['errors' => $global_errors, 'selections' => $selections_errors];
    }

    /**
     * @param $key
     * @param mixed ...$replacer
     * @return array
     */
    protected function getErrorMessage($key, ...$replacer)
    {
        return [
            'code' => self::EXCEPTION_CODES[$key]['code'],
            'message' => sprintf(self::EXCEPTION_CODES[$key]['message'], ...$replacer)
        ];
    }

    /**
     * Determined the winning amount
     *
     * @return bool
     */
    protected function isWinAmountValid()
    {
        $request = request();

        $odds = array_product(array_column($request->selections, 'odds'));
        $winning_amount = $request->stake_amount * $odds;

        return self::MAX_WIN_AMOUNT >= $winning_amount;
    }

    /**
     * @param $name
     * @return mixed|null
     */
    public function getConst($name)
    {
        try {
            return constant("self::{$name}");
        } catch (\Exception $e) {
            return null;
        }
    }
}
