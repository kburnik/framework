<?php

interface IEntityField {
  public function PrimaryKey();
  public function ForeignKey($refTable, $refField);
  public function Integer($size);
  public function Unsigned();
  public function VarChar($size);
  public function Text();
  public function DateTime();
  public function Timestamp();
  public function Date();
  public function Time();
  public function Decimal($total, $decimal);
  public function Double($total, $decimal);
  public function Enum();
  public function AutoEnum($className);
  public function IsNull();
  public function NotNull();
  public function ret();
}

