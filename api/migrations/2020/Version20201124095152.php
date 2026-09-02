<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20201124095152 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql("INSERT IGNORE INTO sectors (location_id, name, inbound_rate, outbound_rate) VALUES
        (9, 'AUTRES', 0,0),
        (9, 'MAG PRINCIPAL', 0,0),
        (9, 'MAG10', 0,0),
        (9, 'MAG20', 0,0),
        (9, 'MAG30', 0,0),
        (9, 'MAG40', 0,0),
        (9, 'MAG50', 0,0),
        (9, 'MAG51', 0,0),
        (9, 'NC', 0,0),
        (9, 'P10', 0,0),
        (9, 'P01', 0,0),
        (9, 'P11', 0,0),
        (9, 'P13', 0,0),
        (9, 'P31', 0,0),
        (9, 'P40', 0,0),
        (9, 'P50', 0,0),
        (9, 'P51', 0,0),
        (9, 'P60', 0,0),
        (9, 'PARKING', 0,0),
        (9, 'PSM', 0,0),
        (9, 'RECEIPT', 0,0),
        (9, 'SOLAR', 0,0),
        (9, 'SYSTEM', 0,0)
        ");

        $this->addSql("UPDATE warehouse_locations SET sector_id=(SELECT id FROM sectors WHERE name ='AUTRES') WHERE erp=520 AND warehouse = 'ST1'
        AND location_number IN ('0','2','3','8','10','35','36','37','38','39','40','10ABS','10JET','10NBL','10TF10','10TF7','10TXL','25E0','CABLE','CATALOGU','CHAMP','ELEC','ET.MAG','ETMAG','EXPR','KB1','KIT HYDR','LIB01','LIB02','LIB03','LIB04','LIB05','LIB06','LIB07','LIB08','LIB09','LIB10','LIB11','LIB12','LIB13','LIB14','LIB15','LIB16','LIB17','LIBNER','LMA','O','O1','O2','O3','O4','O5','O6','O7','OO','P14','P14A','P14B','P14C','P14D','P14E','P14F','P14G','PRABS','PRGPU','PRJCT','PRJET','PRMS','PRNBL','PRPS','PRTF10','Q','Q0','Q1','Q2','Q3','Q4','Q5','Q6','Q7','Q8','Q9','QQ','TIVOLI'
        );");
        $this->addSql("UPDATE warehouse_locations SET sector_id=(SELECT id FROM sectors WHERE name ='MAG PRINCIPAL') WHERE erp=520 AND warehouse = 'ST1'
        AND location_number IN ('A','B','C','D','E','ENROULE','F','FILS','FLEX00','FLEX1','FLEX2','FLEX3','FLEX4','FLEX5','FLEX6','FLEX7','FLEX8','FLEX9','G','H','HYD','I','J','K','L','LIVRET','M','M01','M02','M03','M04','M05','M06','M07','M08','M09','M10','N','TUBE-H'
        );");

        $this->addSql("UPDATE warehouse_locations SET sector_id=(SELECT id FROM sectors WHERE name ='MAG10') WHERE erp=520 AND warehouse = 'ST1'
        AND location_number IN ('0000FAQP','0001FAQP','0002FAQP','0003FAQP','12A01','12A02','12A03','12B01','12B02','12B03','12C01','12C02','12C03','12D01','12D02','12D03','12E01','12E02','12E03','12F01','12F02','12F03','13A01','13A02','13A03','13B01','13B02','13B03','13C01','13C02','13C03','13D01','13D02','13D03','13E01','13E02','13E03','13F01','13F02','13F03','14A','14A01','14C03','14E03','15A01','15A10','15A20','15A30','15B10','15B20','15B30','16A01','16A02','16A03','16B01','16B02','16B03','17A01','17A02','17A03','17B01','17B02','17B03','INSPECT','M14A01','M14A02','M15A01','M15A02','MAG10','X','Y','Z','15'
);");
        $this->addSql("UPDATE warehouse_locations SET sector_id=(SELECT id FROM sectors WHERE name ='MAG20') WHERE erp=520 AND warehouse = 'ST1'
        AND location_number IN ('20A01','20A02','20A03','20A04','20A05','20B00','20B01','20B02','20B03','20B04','20B05','20B06','20C00','20C01','20C02','20C03','20C04','20C05','20C06','20D00','20D01','20D02','20D03','20D04','20D05','20D06','20E01','20E02','20E03','20E05','20E07','20F01','20F03','20F05','20F07','20F09','20G01','20G03','20G05','20G07','20G09','20G11','21A00','21A01','21A02','21A03','21A04','21A05','21A06','21A07','21A08','21A09','21A10','21B01','21B02','21B03','21B04','21B05','21B06','21B07','21B08','21B09','21B10','21B11','21C01','21C02','21C03','21C04','21C05','21C06','21C07','21C08','21C09','21C10','21C11','21D01','21D02','21D03','21D04','21D05','21D06','21D07','21D08','21D10','21E01','21E02','21E03','21E04','21E05','21E06','21E07','21E08','21F01','21F02','21F03','21F04','21F05','21F06','21F07','21F08','21G01','21G02','21G03','21G05','21G07','21G09','22A01','22A02','22A03','22A04','22A05','22A06','22A07','22A08','22B01','22B02','22B03','22B04','22B05','22B06','22B07','22B08','22B09','22B10','22C01','22C02','22C03','22C04','22C05','22C06','22C08','22D01','22D02','22D03','22D04','22D05','22D06','22D07','22D08','22E01','22E02','22E03','22E04','22E05','22E06','22E07','22E08','22F01','22F03','22F05','22G01','22G02','22G03','22G05','22G07','22G09','22H01','22H03','22H05','22H07','22H09','22H11','22PR','23A00','23A01','23A03','23A05','23A07','23A09','23A11','23A13','23B01','23B03','23B05','23B07','23B09','23B11','23B13','23B15','23C01','23C03','23C05','23C07','23D01','23D03','23D05','23D07','24D00','24D01','24D03','24D05','24D07','24D09','24D11','24D13','24D15','24D17','25A00','25A01','25A02','25A03','25A04','25A05','25A06','25A07','25A08','25A09','25A10','26A01','26A02','26A03','26A04','26A05','26A06','26A07','27A01','27A02','27A03','27A04','27A05','27A06','27A07','27A08','27A09','27B01','27B02','27B03','27B04','27B05','27B06','27B07','27B08','27B09','27C01','27C02','27C03','27C04','27C05','27C06','27C07','27C08','27C09','27D01','27D02','27D03','27D04','27D05','27D06','27D07','27D08','27D09'
);");
        $this->addSql("UPDATE warehouse_locations SET sector_id=(SELECT id FROM sectors WHERE name ='MAG30') WHERE erp=520 AND warehouse = 'ST1'
        AND location_number IN ('30A01','30A02','30A03','30A04','30A05','30A06','30A07','30A08','30A10','30B01','30B02','30B03','30B04','30B05','30B06','30B08','30B09','30B10','30C01','30C02','30C03','30C04','30C05','30C06','30C07','30C08','30C09','30C10','30C12','30D01','30D02','30D03','30D04','30D05','30D06','30D07','30D08','30D09','30D10','30D11','30E02','30E03','30E04','30E05','30E06','30E07','30E08','30E09','30E10','30E11','30F01','30F02','30F03','30F04','30F05','30F06','30F07','30F08','30F09','30G01','30G02','30G03','30G04','30G05','30G06','30G07','30H03','31A01','31A02','31A03','31A04','31A05','31A06','31A10','31B01','31B02','31B03','31B04','31B05','31B06','31B07','31B08','31C01','31C02','31C03','31C04','31C05','31C07','31C08','31D01','31D02','31D03','31D04','31D05','31D06','31D07','31D09','31D10','32A01','32A02','32A03','32A04','32A05','32A06','32A07','32B01','32B02','32B03','32B04','32B05','32B06','32B07','32B08','32B09','32C01','32C03','32C04','32C05','32C07','32C08','32C09','32C11','32D01','32D03','32D05','32D07','32D08','32D09','32D10','32E01','32E02','32E03','32E04','32E05','32E06','32E07','32E10','32F01','32F02','32F03','32F04','32F05','32F06','32F07','32G01','32G02','32G03','32G04','32G05','32G06','32H01','32H03','32H05','33A02','33A03','33A04','33A06','33A08','34A02','34A04','34A06','34A08','M30A','M30B','M31A00','M31A10','M31A20','M31A30','M31A40','M31A50','M31A60','M31A70','M31A80','M31A90','M31B10','M31B20','M31B30','M31B40','M31B50','M31B60','M31B70','M31B80','M31B90','M31C10','M31C20','M31C30','M31C40','M31C50','M31C60','M31C70','M31C80','M31C90','M32A00','M32A10','M32A20','M32A30','M32A40','M32A50','M32A60','M32A70','M32A80','M32A90','M32B10','M32B20','M32B30','M32B40','M32B50','M32B60','M32B70','M32B80','M32B90','M32C10','M32C20','M32C30','M32C40','M32C50','M32C60','M32C70','M32C80','M32C90','M33A00','M33A10','M33A20','M33A30','M33A40','M33A50','M33A60','M33A70','M33A80','M33A90','M33B10','M33B20','M33B30','M33B40','M33B50','M33B60','M33B70','M33B80','M33B90','M33C10','M33C20','M33C30','M33C40','M33C50','M33C60','M33C70','M33C80','M33C90','M34A00','M34A10','M34A20','M34A30','M34A40','M34A50','M34A60','M34A70','M34A80','M34A90','M34B10','M34B20','M34B30','M34B40','M34B50','M34B60','M34B70','M34B80','M34B90','M34C10','M34C20','M34C30','M34C40','M34C50','M34C60','M34C70','M34C80','M34C90','M35A00','M35A10','M35A20','M35A30','M35A40','M35A50','M35A60','M35A70','M35A80','M35A90','M35B10','M35B20','M35B30','M35B40','M35B50','M35B60','M35B70','M35B80','M35B90','M35C10','M35C20','M35C30','M35C40','M35C50','M35C60','M35C70','M35C80','M35C90','M36A00','M36A10','M36A20','M36A30','M36A40','M36A50','M36A60','M36A70','M36A80','M36A90','M36B10','M36B20','M36B30','M36B40','M36B50','M36B60','M36B70','M36B80','M36B90','M36C10','M36C20','M36C30','M36C40','M36C50','M36C60','M36C70','M36C80','M36C90','M37A','M38A','M38B','M39A1','M39A2','M39B1','M39B2'
);");
        $this->addSql("UPDATE warehouse_locations SET sector_id=(SELECT id FROM sectors WHERE name ='MAG40') WHERE erp=520 AND warehouse = 'ST1'
        AND location_number IN ('40A','40B','40C','40D','40E','40F','40G','40H','40I','40J','40K','40L','40M','40N','40O'
);");
        $this->addSql("UPDATE warehouse_locations SET sector_id=(SELECT id FROM sectors WHERE name ='MAG50') WHERE erp=520 AND warehouse = 'ST1'
        AND location_number IN ('M50A10','M50A20','M50A30','M50A40','M50A50','M50A60','M50A70','M50B10','M50B20','M50B30','M50B40','M50B50','M50B60','M50B70','M50C10','M50C20','M50C30','M50C40','M50C50','M50C60','M50C70','M50D10','M50D20','M50D30','M50D40','M50D50','M50D60','M50D70','M50E10','M50E20','M50E30','M50E40','M50E50','M50E60','M50E70','M50F10','M50F20','M50F30','M50F40','M50F50','M50F60','M50F70','M50G10','M50G20','M50G30','M50G40','M50G50','M50G60','M50G70','M50H10','M50H20','M50H30','M50H40','M50H50','M50H60','M50H70','M50I10','M50I20','M50I30','M50I40','M50I50','M50I60','M50I70'
);");
        $this->addSql("UPDATE warehouse_locations SET sector_id=(SELECT id FROM sectors WHERE name ='MAG51') WHERE erp=520 AND warehouse = 'ST1'
        AND location_number IN ('M51A10','M51A20','M51A30','M51A40','M51A50','M51A60','M51A70','M51B10','M51B20','M51B30','M51B40','M51B50','M51B60','M51B70','M51C10','M51C20','M51C30','M51C40','M51C50','M51C60','M51C70','M51D10','M51D20','M51D30','M51D40','M51D50','M51D60','M51D70','M51E10','M51E20','M51E30','M51E40','M51E50','M51E60','M51E70','M51F10','M51F20','M51F30','M51F40','M51F50','M51F60','M51F70','M51G10','M51G20','M51G30','M51G40','M51G50','M51G60','M51G70','M51H10','M51H20','M51H30','M51H40','M51H50','M51H60','M51H70','M51I10','M51I20','M51I30','M51I40','M51I50','M51I60','M51I70','M51J10','M51J20','M51J30','M51J30A','M51J30B','M51J30C','M51J30D','M51J30E','M51J30F','M51J40','M51J50','M51K10','M51K20','M51K30','M51K40','M51L10','M51L20','M51L30','M51L40','M51L50'
);");
        $this->addSql("UPDATE warehouse_locations SET sector_id=(SELECT id FROM sectors WHERE name ='NC') WHERE erp=520 AND warehouse = 'ST1'
        AND location_number IN ('NC','NCS','ZNC'
);");
        $this->addSql("UPDATE warehouse_locations SET sector_id=(SELECT id FROM sectors WHERE name ='P01') WHERE erp=520 AND warehouse = 'ST1'
        AND location_number IN ('P01'
);");
        $this->addSql("UPDATE warehouse_locations SET sector_id=(SELECT id FROM sectors WHERE name ='P10') WHERE erp=520 AND warehouse = 'ST1'
        AND location_number IN ('P10'
);");
        $this->addSql("UPDATE warehouse_locations SET sector_id=(SELECT id FROM sectors WHERE name ='P11') WHERE erp=520 AND warehouse = 'ST1'
        AND location_number IN ('P11','P11A','P11B','P11C','P11D','P11E','P11F','P11G','P11H','P11I','P11J'
);");
        $this->addSql("UPDATE warehouse_locations SET sector_id=(SELECT id FROM sectors WHERE name ='P13') WHERE erp=520 AND warehouse = 'ST1'
        AND location_number IN ('HUILE','P13','P13A','P13B'
);");
        $this->addSql("UPDATE warehouse_locations SET sector_id=(SELECT id FROM sectors WHERE name ='P31') WHERE erp=520 AND warehouse = 'ST1'
        AND location_number IN ('SYSTEM','P31','P31OBS');");

        $this->addSql("UPDATE warehouse_locations SET sector_id=(SELECT id FROM sectors WHERE name ='P40') WHERE erp=520 AND warehouse = 'ST1'
        AND location_number IN ('MS','P40'
);");
        $this->addSql("UPDATE warehouse_locations SET sector_id=(SELECT id FROM sectors WHERE name ='P50') WHERE erp=520 AND warehouse = 'ST1'
        AND location_number IN ('P50','P50A','P50B','P50C','P50D','P50E','P50F','P50G','P50H'
);");
        $this->addSql("UPDATE warehouse_locations SET sector_id=(SELECT id FROM sectors WHERE name ='P51') WHERE erp=520 AND warehouse = 'ST1'
        AND location_number IN ('P51','P51A','PARC51'
);");
        $this->addSql("UPDATE warehouse_locations SET sector_id=(SELECT id FROM sectors WHERE name ='P60') WHERE erp=520 AND warehouse = 'ST1'
        AND location_number = 'P60'
;");
        $this->addSql("UPDATE warehouse_locations SET sector_id=(SELECT id FROM sectors WHERE name ='PARKING') WHERE erp=520 AND warehouse = 'ST1'
        AND location_number = 'PARKING'
;");
        $this->addSql("UPDATE warehouse_locations SET sector_id=(SELECT id FROM sectors WHERE name ='PSM') WHERE erp=520 AND warehouse = 'ST1'
        AND location_number  IN ('PSM','PSM/ZNC'
);");
        $this->addSql("UPDATE warehouse_locations SET sector_id=(SELECT id FROM sectors WHERE name ='RECEIPT') WHERE erp=520 AND warehouse = 'ST1'
        AND location_number = 'RECEIPT'
;");
        $this->addSql("UPDATE warehouse_locations SET sector_id=(SELECT id FROM sectors WHERE name ='SOLAR') WHERE erp=520 AND warehouse = 'ST1'
        AND location_number = 'SOLAR'
;");
        $this->addSql("UPDATE warehouse_locations SET sector_id=(SELECT id FROM sectors WHERE name ='SYSTEM') WHERE erp=520 AND warehouse = 'ST1'
        AND location_number = 'SYSTEM'
;");
    }

    public function down(Schema $schema): void
    {
    }
}
