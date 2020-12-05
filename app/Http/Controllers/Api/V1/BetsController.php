<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\BetRequest;
use App\Models\Bet;
use App\Models\Player;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class BetsController extends Controller
{

    /**
     * @param BetRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(BetRequest $request)
    {
        DB::beginTransaction();

        try {
            //Store Bet Details
            $bet = Bet::create(['stake_amount' => $request->stake_amount]);
            $this->storeSelection($bet, $request);

            //Player transaction
            $player = $this->updateOrCreate($request);
            $player->transactions()->create([
                'amount' => $request->stake_amount,
                'amount_before' => $player->balance + $request->stake_amount
            ]);

            DB::commit();

            return \response()->json(null, Response::HTTP_CREATED);
        } catch (\Exception $exception) {
            DB::rollBack();

            throw new HttpResponseException(
                response()->json([
                    [
                        'errors' => BetRequest::EXCEPTION_CODES['unknown'],
                        'selection' => []
                    ]
                ], Response::HTTP_UNPROCESSABLE_ENTITY)
            );
        }
    }

    /**
     * Player balance update If player exits else create player and Update the balance
     *
     * @param $request
     * @return mixed
     */
    protected function updateOrCreate($request)
    {
        $player = Player::find($request->player_id);

        if ($player) {
            $player->updateBalance($request->stake_amount);
            return $player->refresh();
        }

        Player::create(['balance' => Player::DEFAULT_BALANCE])->updateBalance($request->stake_amount);

        return $player->refresh();
    }

    /**
     * @param Bet $bet
     * @param $request
     */
    public function storeSelection(Bet $bet, Request $request)
    {
        $selections = $request->selections;

        foreach ($selections as $selection) {
            $bet->selections()->create([
                'selection_id' => $selection['id'],
                'odds' => $selection['odds']
            ]);
        }
    }

}
