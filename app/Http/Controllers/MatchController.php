<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Input;
// use Illuminate\Support\Facades\Storage;
// use Illuminate\Support\Facades\DB;
use App\User;
use App\Matches;

class MatchController extends Controller {

    static private $blankBoard= [
        0, 0, 0,
        0, 0, 0,
        0, 0, 0
    ];

    static private $matches;

    public function __construct()
    {
        self::$matches= new Matches();
    }

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
        // database
        // $matches= DB::table('matches')->select('id', 'name', 'next', 'winner', 'board')->get();
        $matches= self::$matches->getAllMatches();
        return response()->json($matches);

        // faker
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
        // database
        /*
        $match= DB::table('matches')->select('id', 'name', 'next', 'winner', 'board')
            ->where('id', $id)
            ->get();
        $match= $match->pop();
        $match->board= json_decode($match->board);
        */
        $match= self::$matches->getMatch($id);
        // dump($match);
        return response()->json($match);
        
        /* // faker
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
     * reset a match - for test case
     *
     * @param int $id
     * @return void
     */
    public function reset($id)
    {
        self::$matches->reset($id);
        return $this->match($id);
    }

    /**
     * Makes a move in a match
     *
     * TODO it's mocked, make this work :)
     *
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function move($id) 
    {
        //database
        /*
        $match= DB::table('matches')->select('id', 'name', 'next', 'winner', 'board')
            ->where('id', $id)
            ->get();
        $match= $match->pop();
        $match->board= json_decode($match->board);
        
        $position = Input::get('position');
        // to avoid over take position
        if ($match->board[$position] == 0) {
            $match->board[$position]= $match->next;

            $match->winner= $this->testWinner($match->board);
            $match->next= ($match->winner == 0)? (($match->next == 1)? 2: 1): 0;

            DB::table('matches')->where('id', $id)
                ->update([
                    'next'=> $match->next,
                    'winner'=> $match->winner,
                    'board'=> json_encode($match->board),
                ]);
        }
        */
        $position = Input::get('position');
        $match= self::$matches->matchMove($id, $position);

        return response()->json($match);
        
        /* // faker
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
        ************
        if ($board[0] == $board[1] and $board[0] == $board[2]) return $board[0]; // row 1
        if ($board[3] == $board[4] and $board[3] == $board[5]) return $board[3]; // row 2
        if ($board[6] == $board[7] and $board[6] == $board[8]) return $board[6]; // row 3
        if ($board[0] == $board[3] and $board[0] == $board[6]) return $board[0]; // column 1
        if ($board[1] == $board[4] and $board[1] == $board[7]) return $board[1]; // column 2
        if ($board[2] == $board[5] and $board[2] == $board[8]) return $board[2]; // column 3
        if ($board[0] == $board[4] and $board[0] == $board[8]) return $board[0]; // left to right
        if ($board[2] == $board[4] and $board[2] == $board[6]) return $board[2]; // right to left
        if (!in_array(0, $board)) return 3;
        */
        return 0;
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
        // database
        /*
        $id= DB::table('matches')->insertGetId([
            'name'=>'temp',
            'next'=> 1,
            'winner'=> 0,
            'board'=> json_encode(self::$blankBoard),
        ]);

        DB::table('matches')->where('id', $id)->update(['name'=>"Match:[{$id}]"]);
        */
        self::$matches->createMatch();

        return $this->matches();

        // faker
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
    public function delete($id) 
    {
        // database
        // DB::table('matches')->where('id', $id)->delete();
        self::$matches->deleteMatch($id);

        return $this->matches();

        /* // faker
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
