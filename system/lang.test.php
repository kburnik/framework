<?
include_once("system.php");
include_once("lang.php");

$master_db = sql()->database();
$languages = lang()->supported;

// polja koja čine razliku su tipa: varchar / text

// tables of interest
// 
// fields of interest

// translation table:
/*
	prijevodna tablica (za jedan jezik)
	TABLE ID FIELD   VALUE
	<-uniq-> <index>
	
	kandidati za strukturu 
		1) jedna tablica :: u master bazi
		translations:{lang,table,id,field,value}
		
		2) tablica za svaki jezik pojedinacno :: u prijevodnoj bazi
		tbl_l1:{table,id,field,value}
		tbl_l2:{table,id,field,value}
		...
		
		3) za svaki jezik i svaku tablicu, zasebna jezicna tablica
		
	
	
	možda bi se sve moglo riješiti putem viewova??? :-)
	
	Ali: problem inserta i updatea! A opet:: što sa viewovima? 
	Za insert i update: treba provjeriti... 
	Za viewove:: pa i nema nekog problema, jer tako i tako mogu referencirati surogate (jezične tablice)... možda problem optimizacije...
	
	Dakle izvornu strukturu čuvati u jednoj bazi, a u ostalim održavati viewove
	U master bazi čuvati zapise defaultnog jezika
	U slave bazama držati viewove koji povlače sve nejezična polja iz pripadne master tablice
	a sva jezična polja (prijevode) držati u jednoj tablici

	Treba riješiti pitanje text polja koja su jezična, a koja nisu
	pretpostavka: defaultno sva polja tipa varchar i text su jezična...
*/ 


$ll = new _language_layer($master_db,$languages);

echo "<pre>";

// echo $ll->drop_language_layers(true);
// echo htmlentities($ll->create_structure(true));

echo $ll->lang_field_editor();

echo "</pre>";

?>