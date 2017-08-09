<?php

class qExecutionTime
{

  private $startTime;
  private $endTime;

  public function Start()
  {
    $this->startTime = getrusage();
  }

  public function End()
  {
    $this->endTime = getrusage();
  }

  private function runTime($ru, $rus, $index)
  {
    return ($ru["ru_$index.tv_sec"] * 1000 + intval($ru["ru_$index.tv_usec"] / 1000)) - ($rus["ru_$index.tv_sec"] * 1000 + intval($rus["ru_$index.tv_usec"] / 1000));
  }

  public function __toString()
  {
    $ut = $this->runTime($this->endTime, $this->startTime, "utime");
    $st = $this->runTime($this->endTime, $this->startTime, "stime");

    return
      $ut . " ms for its computations<br>" .
      $st . " ms in system calls<br>".
      ($ut + $st) . " ms total";
  }

}
