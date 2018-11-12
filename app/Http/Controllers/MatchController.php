<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Storage;
use App\User;

class MatchController extends Controller {

    static private $blankBoard= [
        0, 0, 0,
        0, 0, 0,
        0, 0, 0
    ];

    public function index() {
        return view('index');
    }

    /**
     * Returns a list of matches
     *
     * TODO it's mocked, make this work :) - DONE
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function matches()
    {
        if (Storage::disk('local')->exists('matches.dat')) {
          $matches= Storage::disk('local')->get('matches.dat');
          $matches= collect(json_decode($matches));
        } else {
          $matches= null;
        }

        return response()->json($matches);
        // return response()->json($this->fakeMatches());
    }

    /**
     * Returns the state of a single match
     *
     * TODO it's mocked, make this work :) - DONE
     *
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function match($id) 
    {
        /**/ 
        $matches= Storage::disk('local')->get('matches.dat');
        $matches= collect(json_decode($matches));

        $match= $matches->where('id', $id);
        $match= $match->pop();

        return response()->json($match);
        /*
        return response()->json([
            'id' => $id,
            'name' => 'Match'.$id,
            'next' => 2,
            'winner' => 0,
            'board' => [
                1, 0, 2,
                0, 1, 2,
                0, 0, 0,
            ],
        ]);
        */
    }

    /**
     * Makes a move in a match
     *
     * TODO it's mocked, make this work :)
     *
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function move($id) {
        $matches= Storage::disk('local')->get('matches.dat');
        $matches= collect(json_decode($matches));

        $match= $matches->where('id', $id);
        $match= $match->pop();

        $position = Input::get('position');
        $match->board[$position]= $match->next;
        
        $match->winner= $this->testWinner($match->board);
        $match->next= ($match->winner == 0)? (($match->next == 1)? 2: 1): 0;

        $this->delete($id);
        
        $matches= Storage::disk('local')->get('matches.dat');
        $matches= collect(json_decode($matches));

        $matches->push($match);

        Storage::disk('local')->put('matches.dat', $matches->toJson());

        return response()->json($match);

        /*
        $board = [
            1, 0, 2,
            0, 1, 2,
            0, 0, 0,
        ];

        $position = Input::get('position');
        $board[$position] = 2;

        return response()->json([
            'id' => $id,
            'name' => 'Match'.$id,
            'next' => 1,
            'winner' => 0,
            'board' => $board,
        ]);
        */
    }

    /**
     * test to find a winner in the board
     * 
     * @return int (0, 1, 2 or 3 draw)
     */
    private function testWinner($board) {
        /*
            0 1 2
            3 4 5
            6 7 8
        */
        if ($board[0] == $board[1] and $board[0] == $board[2]) return $board[0]; // row 1
        if ($board[3] == $board[4] and $board[3] == $board[5]) return $board[3]; // row 2
        if ($board[6] == $board[7] and $board[6] == $board[8]) return $board[6]; // row 3
        if ($board[0] == $board[3] and $board[0] == $board[6]) return $board[0]; // column 1
        if ($board[1] == $board[4] and $board[1] == $board[7]) return $board[1]; // column 2
        if ($board[2] == $board[5] and $board[2] == $board[8]) return $board[2]; // column 3
        if ($board[0] == $board[4] and $board[0] == $board[8]) return $board[0]; // left to right
        if ($board[2] == $board[4] and $board[2] == $board[6]) return $board[2]; // right to left
        if (!in_array(0, $board)) return 3;
        return 0;
    }

    public function reset($id)
    {
        $matches= Storage::disk('local')->get('matches.dat');
        $matches= collect(json_decode($matches));

        $match= $matches->where('id', $id);
        $match= $match->pop();

        $match->board= self::$blankBoard;
        $match->winner= 0;
        $match->next= 1;

        $this->delete($id);
        
        $matches= Storage::disk('local')->get('matches.dat');
        $matches= collect(json_decode($matches));

        $matches->push($match);

        Storage::disk('local')->put('matches.dat', $matches->toJson());

        return response()->json($match);
    }

    /**
     * Creates a new match and returns the new list of matches
     *
     * TODO it's mocked, make this work :) - DONE
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function create()
    {
      if (Storage::disk('local')->exists('matches.dat')) {
        $matches= Storage::disk('local')->get('matches.dat');
        $matches= collect(json_decode($matches));
      } else {
        Storage::disk('local')->put('matches.dat', '');
        $matches= collect([]);
      }

      if (Storage::disk('local')->exists('matchId.dat')) {
          $matchId= Storage::disk('local')->get('matchId.dat');
          $matchId= (int)$matchId + 1;
      } else {
          $matchId= 1;
      }
      Storage::disk('local')->put('matchId.dat', $matchId);

      $matches->push([
          'id' => $matchId,
          'name' => "Match:[{$matchId}]",
          'next' => 1,
          'winner' => 0,
          'board' => self::$blankBoard,
      ]);

      Storage::disk('local')->put('matches.dat', $matches->toJson());

      return response()->json($matches->all());
      // return response()->json($this->fakeMatches());
    }

    /**
     * Deletes the match and returns the new list of matches
     *
     * TODO it's mocked, make this work :) - DONE
     *
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id) {
        if (Storage::disk('local')->exists('matches.dat')) {
            $matches= Storage::disk('local')->get('matches.dat');
            $matches= collect(json_decode($matches));
        } else {
            return response()->json();
        }
        
        $matches= $matches->filter(function($match) use($id){
            return $match->id != $id;
        })->values();

        Storage::disk('local')->put('matches.dat', $matches->toJson());

        return response()->json($matches->all());
    
        /*
        return response()->json($this->fakeMatches()->filter(function($match) use($id){
            return $match['id'] != $id;
        })->values());
        */
    }

    /**
     * Creates a fake array of matches
     *
     * @return \Illuminate\Support\Collection
     */
    private function fakeMatches() {
        return collect([
            [
                'id' => 1,
                'name' => 'Match1',
                'next' => 2,
                'winner' => 1,
                'board' => [
                    1, 0, 2,
                    0, 1, 2,
                    0, 2, 1,
                ],
            ],
            [
                'id' => 2,
                'name' => 'Match2',
                'next' => 1,
                'winner' => 0,
                'board' => [
                    1, 0, 2,
                    0, 1, 2,
                    0, 0, 0,
                ],
            ],
            [
                'id' => 3,
                'name' => 'Match3',
                'next' => 1,
                'winner' => 0,
                'board' => [
                    1, 0, 2,
                    0, 1, 2,
                    0, 2, 0,
                ],
            ],
            [
                'id' => 4,
                'name' => 'Match4',
                'next' => 2,
                'winner' => 0,
                'board' => [
                    0, 0, 0,
                    0, 0, 0,
                    0, 0, 0,
                ],
            ],
        ]);
    }

}
