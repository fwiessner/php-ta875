<?php

class ta875
{

    private $lastschriftforderungen = array();

    private $totalbetrag = 0;

    public function __construct($erstellungsdatum, $absenderIdentifikation,  $waehrung)
    {
        $this->erstellungsdatum = $erstellungsdatum;
        $this->absenderIdentifikation = $absenderIdentifikation;
        $this->eingabeSequenznummer = 1;
        $this->waehrung = $waehrung;
        $this->verarbeitungsart = "P" ; // P = production, T = testmode
    }

    public function generateTA875()
    {
        $ta875 = '';
        foreach ($this->lastschriftforderungen as $forderung) {
            $ta875 .= $forderung;
        }
        $ta875 .= $this->createTotalrecord();
        return $ta875;
    }

    public function addLastschriftrecord($lastschriftrecord)
    {
     
            
        $transaktionsart = "875";
        $versionsNr = "0";
        $verarbeitungsart = $this->verarbeitungsart ;
        $gewuenschtesVerarbeitungsdatum = $lastschriftrecord['Gewünschtes Verarbeitungsdatum'];
        $iidLastschriftzahler = $lastschriftrecord['IID des Lastschriftzahlers'];
        $erstellungsdatum = $lastschriftrecord['Erstellungsdatum'];
        $iidRechnungssteller = $lastschriftrecord['IID des Rechnungsstellers'];
        $absenderIdentifikation = $this->absenderIdentifikation ;
        $LSVIdentifikation = $lastschriftrecord['LSV-Identifikation'];
        
        $waehrung = $lastschriftrecord['Währung'];
        $betrag = $lastschriftrecord['Betrag'];
        $kontoRechnungssteller = $lastschriftrecord['Konto des Rechnungsstellers'];
        $rechnungsstellerAdresse = $lastschriftrecord['Rechnungssteller-Adresse'];
        $kontoLastschriftzahler = $lastschriftrecord['Konto des Lastschriftzahlers'];
        $lastschriftzahlerAdresse = $lastschriftrecord['Lastschriftzahler-Adresse'];
        $mitteilungen = $lastschriftrecord['Mitteilungen'];
        $referenzFlag = $lastschriftrecord['Referenz-Flag'];
        $lsvReferenz = $lastschriftrecord['LSV-Referenz'];
        $esrTeilnehmerNummer = $lastschriftrecord['ESR-Teilnehmernummer'];
        
        // Formatieren der einzelnen Felder gemäß der Länge
        $transaktionsart = str_pad($transaktionsart, 3, '0', STR_PAD_LEFT);
        $versionsNr = str_pad($versionsNr, 1, '0', STR_PAD_LEFT);
        $verarbeitungsart = strtoupper($verarbeitungsart);
        $gewuenschtesVerarbeitungsdatum = str_pad($gewuenschtesVerarbeitungsdatum, 8, '0', STR_PAD_LEFT);
        $iidLastschriftzahler = str_pad($iidLastschriftzahler, 5);
        $erstellungsdatum = str_pad($erstellungsdatum, 8, '0', STR_PAD_LEFT);
        $iidRechnungssteller = str_pad($iidRechnungssteller, 5);
        $absenderIdentifikation = str_pad($absenderIdentifikation, 5);
        
        
        $eingabeSequenznummer = str_pad($this->eingabeSequenznummer, 7, '0', STR_PAD_LEFT);
        $this->eingabeSequenznummer = str_pad($this->eingabeSequenznummer, 7, '0', STR_PAD_LEFT);
        
        $waehrung = strtoupper($waehrung);
        $betrag_calc_intern = str_replace(",",".",$betrag); 
        
        $betrag = str_pad($betrag, 12, '0', STR_PAD_LEFT);
        $kontoRechnungssteller = str_pad($kontoRechnungssteller, 34);
        $rechnungsstellerAdresse = ta875::formatMultiLineText($rechnungsstellerAdresse);
        $kontoLastschriftzahler = str_pad($kontoLastschriftzahler, 34);
        $lastschriftzahlerAdresse = ta875::formatMultiLineText($lastschriftzahlerAdresse);
        $mitteilungen = ta875::formatMultiLineText($mitteilungen);
        $referenzFlag = strtoupper($referenzFlag);
        $lsvReferenz = str_pad($lsvReferenz, 27);
        $esrTeilnehmerNummer = str_pad($esrTeilnehmerNummer, 9);

        // Zusammenstellen des Lastschriftrecords
        $lastschriftrecord = $transaktionsart . $versionsNr . $verarbeitungsart . $gewuenschtesVerarbeitungsdatum . $iidLastschriftzahler . $erstellungsdatum . $iidRechnungssteller . $absenderIdentifikation . $eingabeSequenznummer . $LSVIdentifikation. $waehrung . $betrag . $kontoRechnungssteller . $rechnungsstellerAdresse . $kontoLastschriftzahler . $lastschriftzahlerAdresse . $mitteilungen . $referenzFlag . $lsvReferenz . $esrTeilnehmerNummer;

        
        $this->eingabeSequenznummer++;
        $this->eingabeSequenznummer = str_pad($this->eingabeSequenznummer, 7, '0', STR_PAD_LEFT);
        array_push($this->lastschriftforderungen, $lastschriftrecord . "\n");
        $this->totalbetrag += $betrag_calc_intern;
    }

    // Hilfsfunktion zum Formatieren von mehrzeiligem Text
    private function formatMultiLineText($text)
    {
        $lines = explode("\n", $text);
        $formattedText = '';
        for ($i = 0; $i < 4; $i ++) {
            if (isset($lines[$i])) {
                $formattedText .= str_pad($lines[$i], 35);
            } else {
                $formattedText .= str_pad('', 35);
            }
        }
        return $formattedText;
    }

    private function createTotalrecord()
    {
        $TA = "890";
        $version = "0";

        // Überprüfen der Feldlängen
        if (strlen($this->erstellungsdatum) !== 8) {
            throw new Exception('Fehler: Das Erstellungsdatum hat eine ungültige Länge.');
        }
        if (strlen($this->absenderIdentifikation) !== 5) {
            throw new Exception('Fehler: Die Absender-Identifikation hat eine ungültige Länge.');
        }
        if (strlen($this->eingabeSequenznummer) !== 7) {
            throw new Exception('Fehler: Die Eingabe-Sequenznummer hat eine ungültige Länge.');
        }
        if (strlen($this->waehrung) !== 3) {
            throw new Exception('Fehler: Die Währung hat eine ungültige Länge.');
        }

        $totalrecord = $TA . $version . $this->erstellungsdatum . $this->absenderIdentifikation . $this->eingabeSequenznummer . $this->waehrung;

        // Überprüfen, ob der Totalbetrag numerisch ist
        if (! is_numeric($this->totalbetrag)) {
            throw new Exception('Fehler: Der Totalbetrag ist nicht numerisch.');
        }

        // Formatieren des Gesamtbetrags gemäß der Länge
        $totalbetragFormatted = str_pad(number_format($this->totalbetrag, 2, ',', ''), 16, '0', STR_PAD_LEFT);
        $totalrecord .= $totalbetragFormatted;
        // Überprüfen der Feldlängen
        if (strlen($totalrecord) !== 43) {
            throw new Exception('Fehler: Der Totalrecord hat eine ungültige Gesamtlänge:' . strlen($totalrecord) . " => $totalrecord");
        }
        if (strlen($totalbetragFormatted) !== 16) {
            throw new Exception('Fehler: Der formatierte Totalbetrag hat eine ungültige Länge.');
        }



        return $totalrecord;
    }

    function validate_ta875($ta875_file_contents)
    {
        $lines = explode("\n", $ta875_file_contents);



        $errors = array();

        foreach ($lines as $line_number => $line) {
            $record_type = substr($line, 0, 3);

            if ($record_type === '890') {
                // Validierung des Totalrecords (TA890)
                $totalrecord = $line;
                // Überprüfen der Feldlängen im Totalrecord
                $transaktionsart = substr($totalrecord, 0, 3);
                $versionsNr = substr($totalrecord, 3, 1);
                $erstellungsdatum = substr($totalrecord, 4, 8);
                $absenderIdentifikation = substr($totalrecord, 12, 5);
                $eingabeSequenznummer = substr($totalrecord, 17, 7);
                $waehrung = substr($totalrecord, 24, 3);
                $totalbetrag = substr($totalrecord, 27, 16);

                if ($transaktionsart !== '890') {
                    $errors[] = "Fehler in TA890 (Totalrecord): Transaktionsart ist ungültig.";
                }

                if ($versionsNr !== '0') {
                    $errors[] = "Fehler in TA890 (Totalrecord): Versionsnummer ist ungültig.";
                }

                if (strlen($erstellungsdatum) !== 8) {
                    $errors[] = "Fehler in TA890 (Totalrecord): Erstellungsdatum hat eine ungültige Länge.";
                }

                if (strlen($absenderIdentifikation) !== 5) {
                    $errors[] = "Fehler in TA890 (Totalrecord): Absender-Identifikation hat eine ungültige Länge.";
                }

                if (strlen($eingabeSequenznummer) !== 7) {
                    $errors[] = "Fehler in TA890 (Totalrecord): Eingabe-Sequenznummer hat eine ungültige Länge.";
                }

                if (strlen($waehrung) !== 3) {
                    $errors[] = "Fehler in TA890 (Totalrecord): Währung hat eine ungültige Länge.";
                }

                if (strlen($totalbetrag) !== 16) {
                    $errors[] = "Fehler in TA890 (Totalrecord): Totalbetrag hat eine ungültige Länge.";
                }

                // Weitere Validierungen für spezifische Felder im Totalrecord (TA890)
                if (! preg_match('/^\d{8}$/', $erstellungsdatum)) {
                    $errors[] = "Ungültiges Erstellungsdatum in TA890 (Totalrecord) in Zeile " . ($line_number + 1);
                }
                if (! preg_match('/^[A-Z0-9]{5}$/', $absenderIdentifikation)) {
                    $errors[] = "Ungültige Absender-Identifikation in TA890 (Totalrecord) in Zeile " . ($line_number + 1);
                }
            } elseif ($record_type === '875') {
                // Validierung der TA875-Records
                $record = $line;
                $versionsNr = substr($record, 3, 1);
                $verarbeitungsart = substr($record, 4, 1);
                $gewuenschtesVerarbeitungsdatum = substr($record, 5, 8);
                $iidLastschriftzahler = substr($record, 13, 5);
                $erstellungsdatum = substr($record, 18, 8);
                $iidRechnungssteller = substr($record, 26, 5);
                $absenderIdentifikation = substr($record, 31, 5);
                $eingabeSequenznummer = substr($record, 36, 7);
                $lsvIdentifikation = substr($record, 43, 5);
                $waehrung = substr($record, 48, 3);
                $betrag = substr($record, 51, 12);
                $kontoRechnungssteller = substr($record, 63, 34);
                $adrRechnungssteller = substr($record, 97, 140);
                $kontoLastschriftzahler = substr($record, 237, 34);
                $adrLastschriftzahler = substr($record, 271, 140);
                $mitteilungen = substr($record, 411, 140);
                $referenzFlag = substr($record, 551, 1);
                $lsvReferenz = substr($record, 552, 27);
                $esrTeilnehmernummer = substr($record, 579, 9);
                
                
                // Validierung der Feldlängen und Feldtypen
                $field_validations = [
                    ['Versions-Nr.', $versionsNr, 1, 'n', false],
                    ['Verarbeitungsart', $verarbeitungsart, 1, 'x', true, ['P', 'T']],
                    ['Gewünschtes Verarbeitungsdatum', $gewuenschtesVerarbeitungsdatum, 8, 'n', false],
                    ['IID des Lastschriftzahlers', $iidLastschriftzahler, 5, 'x', true],
                    ['Erstellungsdatum', $erstellungsdatum, 8, 'n', false],
                    ['IID des Rechnungsstellers', $iidRechnungssteller, 5, 'x', true],
                    ['Absender-Identifikation', $absenderIdentifikation, 5, 'x', true],
                    ['Eingabe-Sequenznummer', $eingabeSequenznummer, 7, 'n', false],
                    ['LSV-Identifikation', $lsvIdentifikation, 5, 'x', true],
                    ['Währung', $waehrung, 3, 'x', true],
                    ['Betrag', $betrag, 12, 'x', false],
                    ['Konto des Rechnungsstellers', $kontoRechnungssteller, 34, 'x', true],
                    ['Rechnungssteller-Adresse', $adrRechnungssteller, 4 * 35, 'x', true],
                    ['Konto des Lastschriftzahlers', $kontoLastschriftzahler, 34, 'x', true],
                    ['Lastschriftzahler-Adresse', $adrLastschriftzahler, 4 * 35, 'x', true],
                    ['Mitteilungen', $mitteilungen, 4 * 35, 'x', true],
                    ['Referenz-Flag', $referenzFlag, 1, 'x', true, ['A', 'B']],
                    ['LSV-Referenz', $lsvReferenz, 27, 'x', true],
                    ['ESR-Teilnehmernummer', $esrTeilnehmernummer, 9, 'x', true]
                ];
                
                foreach ($field_validations as $field_validation) {
                    $field_name = $field_validation[0];
                    $field_value = $field_validation[1];
                    $expected_length = $field_validation[2];
                    $expected_type = $field_validation[3];

                    
                    // Validierung der Feldlänge
                    if (strlen($field_value) !== $expected_length) {
                        $errors[] = "Formatfehler in TA875 (Transaktionsart 875) in Zeile " . ($line_number + 1) . ": Ungültige Feldlänge für " . $field_name . ": ". strlen($field_value) ." !== " .  $expected_length. ".";
                    }
                    
                    // Validierung des Feldtyps
                    if (!is_null($field_value) && $expected_type === 'n' && !ctype_digit($field_value)) {
                        $errors[] = "Formatfehler in TA875 (Transaktionsart 875) in Zeile " . ($line_number + 1) . ": Ungültiger Wert für " . $field_name . ": $field_value.";
                    } elseif (!is_null($field_value) && $expected_type === 'x' && !ctype_print($field_value)) {
                        $errors[] = "Formatfehler in TA875 (Transaktionsart 875) in Zeile " . ($line_number + 1) . ": Ungültiger Wert für " . $field_name . ": $field_value.";
                    }
                    

                }
            } else {
                $errors[] = "Unbekannter Record-Typ in Zeile " . ($line_number + 1);
            }
        }

        return $errors;
    }
}


$erstellungsdatum = "20230524";
$absenderIdentifikation = "ABSID";
$waehrung = "CHF";

$ta = new ta875($erstellungsdatum = "20230524", $absenderIdentifikation = "ABSID",  $waehrung = "CHF");

$lastschriftrecord = [

    'Gewünschtes Verarbeitungsdatum' => '20171124',
    'IID des Lastschriftzahlers' => '6182',
    'Erstellungsdatum' => $erstellungsdatum,
    'IID des Rechnungsstellers' => '202',
    'LSV-Identifikation' => 'ABC1W',
    'Währung' => $waehrung,
    'Betrag' => '0000025156,7',
    'Konto des Rechnungsstellers' => 'CH9300762011623',
    'Rechnungssteller-Adresse' => "Max Meier\nDorfplatz 3\n9999 Irgendwo",
    'Konto des Lastschriftzahlers' => '123.456-78XY',
    'Lastschriftzahler-Adresse' => "DORIS ENG\nANDERSWO",
    'Mitteilungen' => "Rechnung vom\n31.10.2017",
    'Referenz-Flag' => 'A',
    'LSV-Referenz' => '200002000000004',
    'ESR-Teilnehmernummer' => '010001456'
];

$ta->addLastschriftrecord($lastschriftrecord);

$lastschriftrecord = [

    'Gewünschtes Verarbeitungsdatum' => '20230524',
    'IID des Lastschriftzahlers' => '6182',
    'Erstellungsdatum' => '20230524',
    'IID des Rechnungsstellers' => '202',
    'LSV-Identifikation' => 'ABC1W',
    'Währung' => 'CHF',
    'Betrag' => '0000011116,7',
    'Konto des Rechnungsstellers' => 'CH9300762011623',
    'Rechnungssteller-Adresse' => "Max Meier\nDorfplatz 3\n9999 Irgendwo",
    'Konto des Lastschriftzahlers' => '123.456-78XY',
    'Lastschriftzahler-Adresse' => "DORIS ENG\nANDERSWO",
    'Mitteilungen' => "Rechnung vom\n31.10.2022",
    'Referenz-Flag' => 'A',
    'LSV-Referenz' => '200002000000004',
    'ESR-Teilnehmernummer' => '010001456'
];

$ta->addLastschriftrecord($lastschriftrecord);

$result = $ta->generateTA875();
$errors = $ta->validate_ta875($result);

echo $result . "\n";

var_dump($errors);
