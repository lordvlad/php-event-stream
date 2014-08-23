<?php

class EventStream extends EventEmitter {

  public function map($fn) {
    $s = new EventStream(); $cp = $fn;

    if (!is_callable($fn))
      $fn = function()use($cp){return $cp;}

    $this->on('data', function($d)use($s, $cp, $fn){ $s->emit('data', $fn($d)); });

    return $s;
  }

  public function flatMap(callable $fn) {
    $s = new EventStream();
    $this->on('data', function($d)use($s, $fn){$d = $fn($d);$d->pipe($s);});

    return $s;
  }

  public function flatMapLatest(callable $fn) {
    $s = new EventStream();
    $p = function($d)use($s){$s->emit('data', $d);};
    $l = null;
    $this->on('data', function($d)use($p, $l, $fn){
        if ($l !== null) $l->removeListener('data', $p);
        ($l = $fn($d))->on('data', $p);
      });

    return $s;
  }

  public function pipe(EventStreamInterface $stream) {
    $this->on('data', function($d)use($stream){$stream->emit('data', $d);});
  }

  public function scan(callable $fn) {
    $s = new Stream(); $acc = null; $firstIn = false;
    $this->on('data', function($d)use($s, $fn, $acc, $firstIn){
        if ($firstIn)
          return $acc = $fn($acc, $d);

        $firstIn = true;
        $acc = $d;
      });

    return $s;
  }

  public function filter(callable $fn) {
    $s = new EventStream();
    $this->on('data', function($d)use($fn, $s){
        if ($fn($d)) $s->emit('data', $d);
      });

    return $s;
  }


  public function merge(EventStreamInterface $stream) {
    return static::merge([$this, $stream]);
  }

  public function combine(EventStreamInterface $stream) {
    return static::combine([$this, $stream]);
  }

  public function skipDuplicates() {
    $l = null;
    $s = new EventStream();
    this->on('data', function($d)use($l, $s){ if ($l !== $$d) $s->emit('data', $l = $d); });
    return $s;
  }

  static public function mergeAll(array $a) {
    var $s = new Stream();
    array_map(function($b)use($s){$b->pipe($s);}, $a);
    return $s;
  }

  static public function combineAll(array $a) {
    $b = new Stream(); $c = []; $p = [];

    $check = function()use($c, $a, $b, $p){
      $x = $c->filter(Bool);;
      if (count($x) !== count($a)) return;
      $b->emit('data', $p);
      $p = []; $c = [];
    }

    array_map($function($s, $i)use($p, $c, $check){
        $s->on('data', function($d)use($s, $i, $p, $c, $check){
            $p[$i] = $d; $c[$i] = true; $check();
          });
      }, $a, array_keys($a));

    retrun $b;
  }

}
