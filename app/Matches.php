<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Matches extends Model
{
    /**
     * database table name
     *
     * @var string
     */
    protected $table= 'matches';

    /**
     * get all matches created
     *
     * @return array
     */
    public function getAllMatches(): array
    {
        $matches= $this->select('id', 'name', 'next', 'winner', 'board')->get();

        return $matches->toArray();
    }

    /**
     * return a selected match 
     *
     * @param int $id
     * @return array
     */
    public function getMatch($id): array
    {
        $match= $this->select('id', 'name', 'next', 'winner', 'board')
            ->where('id', $id)
            ->get();
        $match= $match->pop();
        $match->board= json_decode($match->board);

        //dump($match->toArray());
        return $match->toArray();
    }

    /**
     * se a move for a seleted match
     *
     * @param int $id
     * @param int $position
     * @return array
     */
    public function matchMove($id, $position): array
    {
        $match= $this->getMatch($id);
        // to avoid over take position
        //dump($match);
        if ($match['board'][$position] == 0) {
            $match['board'][$position]= $match['next'];

            $match['winner']= $this->testWinner($match['board']);
            $match['next']= ($match['winner'] == 0)? (($match['next'] == 1)? 2: 1): 0;

            $this->where('id', $id)
                ->update([
                    'next'=> $match['next'],
                    'winner'=> $match['winner'],
                    'board'=> json_encode($match['board']),
                ]);
        }

        return $match;

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

    /**
     * create a new match
     *
     * @return void
     */
    public function createMatch()
    {
        $id= $this->insertGetId([
            'name'=>'temp',
            'next'=> 1,
            'winner'=> 0,
            'board'=> json_encode([
                    0, 0, 0,
                    0, 0, 0,
                    0, 0, 0
                ]),
        ]);

        $this->where('id', $id)->update(['name'=>"Match:[{$id}]"]);
    }

    /**
     * delete a existing match
     *
     * @param int $id
     * @return void
     */
    public function deleteMatch($id)
    {
        $this->where('id', $id)->delete();
    }

    public function reset($id)
    {
        $this->where('id', $id)
            ->update([
                'next'=>1,
                'winner'=>0,
                'board'=> json_encode([
                        0, 0, 0,
                        0, 0, 0,
                        0, 0, 0,
                    ]),
            ]);
    }
}
