<?php
header('Content-Type: text/html; charset=utf-8');
$DEFAULT_TITLE .= "\DMS shopfloor";
$rows = array();
$dms = function($id, $desc) {
    return array(
        'id' => $id,
        'desc' => $desc
    );
};

switch($ERP) {
    case '220':
        $rows[] = $dms(236, "Description of the Ethics rules & business conduct of the Group.");
        $rows[] = $dms(314, "This document describes the use of the PDC system, the main principles to apply when using the PDC, the organization of the PDC meeting.");
        $rows[] = $dms(603, "This procedure defines the responsibilities and describes the correctives and preventive action request process and the follow-up regarding the implementation and their efficiency.");
        $rows[] = $dms(604, "This document filled to indicate the nature and the follow-up of the preventive actions and corrective to be done.");
        $rows[] = $dms(814, "This document define the Naming convention for Fasteners. It has to be used by all Engineering department when new fasteners are created in Data base and to rename existing fastener. It will allow us to identify and eliminate duplicated items in data base.");
        $rows[] = $dms(1438, "ALVEST CORE Values to be posted in all ALVEST entities premises along with local language version when needed.");
        $rows[] = $dms(1791, "Corrective & Preventive Actions. 
                                       This document describes the use of the CPA system,the main principles to apply when using the CPA, the organization of the CPA meeting.");
        $rows[] = $dms(1897, "the Steering Committee has implemented a statement regarding compliance matters, this statement shall be used in particular as an introduction to all compliance trainings");
        $rows[] = $dms(2128, "Definition of a CRAB (in production)
                                       How to create a CRAB during production
                                       How to fix it and inspect it.");
        $rows[] = $dms(3054, "whistleblowing Policy to enable all employees to raise concerns about compliance, violations of laws and régulations etc");
        $rows[] = $dms(3188, "This PPT presents the ALVEST group, its strategic priorities and its differents activities.");
        $rows[] = $dms(3609, "Procedure for manual & digital pull testing of crimps");
        $rows[] = $dms(3616, "Procedure for disposition of non-conforming material within factory and from customers. Includes MRB process ");
        $rows[] = $dms(3617, "Procedure for opening, managing, and closing CRABS");
        $rows[] = $dms(3618, "Procedure for raising, managing, and closing NCRs");
        $rows[] = $dms(3620, "Procedure for inspection and applying Green Tag (GT) during QA inspection post test");
        $rows[] = $dms(3622, "Process Flow Chart for Assembly_Test & GT");
        $rows[] = $dms(3630, "Procedure for calibration of measurement and test equipment");
        $rows[] = $dms(3632, "Process Map (turtle diagram) showing interactions for ISO 9001:2015");
        $rows[] = $dms(3633, "Process Map (turtle diagram) for Business Management System (QMS) showing interactions for ISO 9001:2015");
        $rows[] = $dms(3635, "Process Map (turtle diagram) showing ISO 9001:2015 interactions for document control");
        $rows[] = $dms(3636, "Procedure for document control");
        $rows[] = $dms(3652, "Powervamp Organisation Chart");
        $rows[] = $dms(3653, "Document Control register for whole BMS");
        $rows[] = $dms(3668, "Process Map for calibration showing interactions");
        $rows[] = $dms(3669, "Process map showing interactions for non-conforming material");
        $rows[] = $dms(3680, "Process map showing interactions for corrective action");
        $rows[] = $dms(3681, "Process map showing interactions for preventative action");
        $rows[] = $dms(3687, "Health and Safety Policy including Statement of Intent, arrangements and Roles");
        $rows[] = $dms(3700, "Operating procedure for assembly of products");
        $rows[] = $dms(3767, "Procedure how to raise and close out a CPA");
        $rows[] = $dms(3836, "English version of the training material that shall be used for the yearly mandatory Cybersecurity Awareness Training.");
        $rows[] = $dms(3897, "Employee handbook for Powervamp Ltd employees that defines all policies that are related to Employment Law");
        $rows[] = $dms(3829, "Procedure for performing chemical risk assessments under the CoSHH Regulations");

        $rows[] = $dms(4657, "Procedure for measuring & monitoring Customer Satisfaction");
        $rows[] = $dms(4664, "The present document describes the Group Environmental policy. It supports local environmental policy development and implementation across the Group BUs");
        $rows[] = $dms(4679, "Fire Safety Management & Fire Emergency Plan that details steps taken to mitigate fire risk such as inspections for fire extinguishers, emergency lighting, signage, escape routes, training, risk assessments, strategies, etc.");
        $rows[] = $dms(4832, "General Safety Procedure to define rules and regulations within Powervamp Ltd");
        $rows[] = $dms(4870, "This procedure describes the actions to be taken to minimise resource use including electricity, gas and water within the business premises");
        $rows[] = $dms(4873, "ISO 9001 & 14001 Integrated Management System Manual (Quality Manual)");
        $rows[] = $dms(4883, "The purpose of this procedure is to ensure Powervamp Limited mitigate the negative effects as a result of an incident and to ensure workers including contractors under the control of the Company are prepared in respond to incidents.
                                       The requirements of the following Standards have been considered during the development of this procedure:
                                       • ISO 14001 Environmental Management System
                                       • ISO 45001 Occupational Health and Safety Management System (Not certified)");
        $rows[] = $dms(4894, "A presentation to include an overview of safe working practices relating to low voltage(<1000V)storage devices, including mandatory procedures, e.g. use of PPE, insulated tools, safe isolation procedures & safe working practices with such devices.");
        $rows[] = $dms(4912, "Procedure defining the steps to take if a near miss occurs for either Health and Safety, or, Environmental reasons");
        $rows[] = $dms(4913, "Report to document any near miss incident, or, accident for ISO 14001 & 45001");
        $rows[] = $dms(4914, "Register to record all HSE incidents (accidents & near misses & spills)");
        $rows[] = $dms(5670, "ALVEST Work-Life balance principles
                                       Principes de l'équilibre professionnel et personnel au sein d’Alvest
                                       ALVEST工作与生活平衡原则");
        $rows[] = $dms(7234, "This document establishes the guidelines for fasteners and torque. Engineers must follow these guidelines in their design where possible. Deviation must be clearly stated on drawings and PIO. Production must follow these guidelines when building machine unless otherwise stated.");
        $rows[] = $dms(8235, " Describes best practices for torque markings ");
        break;
    case '420':
        $rows[] = $dms(2391, "Final performance test for NBL");
        $rows[] = $dms(483, "Paint Process");
        $rows[] = $dms(491, "Coating Specification");
        $rows[] = $dms(494, "Mouvement des pieces");
        $rows[] = $dms(1596, "Demande de pieces - Maintenance");
        $rows[] = $dms(1799, "P&I Guidelines and Procedures");
        $rows[] = $dms(1854, "P&I User Guide");
        $rows[] = $dms(467, "Work Instruction for Receiving in BaaN");
        $rows[] = $dms(470, "Processus d'Expedition d'Unite");
        $rows[] = $dms(471, "Bill of Lading");
        $rows[] = $dms(472, "Formulaire Memo d'expedition");
        $rows[] = $dms(473, "Emballage");
        $rows[] = $dms(474, "Fixer les pieces du loader sur palette");
        $rows[] = $dms(506, "Procedure de cadenassage");
        $rows[] = $dms(637, "BP - Qualite Per&ccedil;ue");
        $rows[] = $dms(638, "BP - electricite");
        $rows[] = $dms(639, "BP - Montage Fluides");
        $rows[] = $dms(640, "BP - Montage Hydraulique");
        $rows[] = $dms(645, "Valve Coil Assy / ");
        $rows[] = $dms(646, "Installation du bouchon de carburant");
        $rows[] = $dms(648, "verification de l'epaisseur de peinture");
        $rows[] = $dms(649, "Nettoyage des tubes rigides hydraulique /");
        $rows[] = $dms(650, "Inst des arbres hexagonals pour les FTC");
        $rows[] = $dms(651, "NBL Boom Installation");
        $rows[] = $dms(652, "Engine Compartment Adjustemt /");
        $rows[] = $dms(654, "Couple de serrage des boulons /");
        $rows[] = $dms(655, "Standards de boulonnage / ");
        $rows[] = $dms(656, "Sertissage boyaux hydraulique / ");
        $rows[] = $dms(657, "Installation des capteurs de proximite");
        $rows[] = $dms(658, "Ajustement ds chaines a dessu plat (FTC)");
        $rows[] = $dms(659, "Shim of rollers for roller track");
        $rows[] = $dms(660, "TXL-838 Bridge electrique / ");
        $rows[] = $dms(662, "Installation des ecrous rapportes / ");
        $rows[] = $dms(664, "TXL-838 Installation elevateur electrique ");
        $rows[] = $dms(665, "TXL-838 Assemblage Console ");
        $rows[] = $dms(668, "Surfaces Protection");
        $rows[] = $dms(670, "Montage des plaquettes de retenue / ");
        $rows[] = $dms(671, "Bushing Installation");
        $rows[] = $dms(673, "Installation de Valves Hydr");
        $rows[] = $dms(674, "Utilisation du Loctite - compose de retenue");
        $rows[] = $dms(677, "SPLIT CLAMPING COLLAR TIGHTENING");
        $rows[] = $dms(678, "NBL Hyd. Power brake option");
        $rows[] = $dms(682, "Ajustement des taquets arriere du Bridge");
        $rows[] = $dms(683, "Installation des câbles sur le module IFM");
        $rows[] = $dms(684, "Plaque signaletique / Serial Plate");
        $rows[] = $dms(685, "NBL Ajustement Courroie/");
        $rows[] = $dms(686, "Torque pour Bouchons Hydraulique /");
        $rows[] = $dms(688, "Pesee du PFA-50 / Weight of PFA-50");
        $rows[] = $dms(690, "Changement Cylindre de Direction ");
        $rows[] = $dms(691, "Procedure reglage moteur Poclain ");
        $rows[] = $dms(695, "FEDEX NBL Pre-assemble / ");
        $rows[] = $dms(698, "Capteur pression parking brake ");
        $rows[] = $dms(829, "Emballage des blocs hydrauliques/ pack");
        $rows[] = $dms(832, "Manifold cleaning before assembly ");
        $rows[] = $dms(836, "PFA-50: Start-up Close Loop Pump");
        $rows[] = $dms(844, "Poclain motor Oil filling");
        $rows[] = $dms(910, "Pump Kawa startup, open loop");
        $rows[] = $dms(1260, "929 Rear wheel assy");
        $rows[] = $dms(1284, "Sauer pump program");
        $rows[] = $dms(1285, "TLX-838 rear wheel assy");
        $rows[] = $dms(1343, "PFA-50 Start-up engine and hydraulic");
        $rows[] = $dms(1404, "PFA-50 Filtration d'huile");
        $rows[] = $dms(1582, "Protection Anti-rouille PFA-50");
        $rows[] = $dms(1586, "Protection Anti-rouille NBL");
        $rows[] = $dms(1782, "Protection Anti-rouille Loaders");
        $rows[] = $dms(2355, "Procedure pour valider le fonctionnement de l'ASD");
        $rows[] = $dms(2882, "Marquage des raccords hydraulique");
        $rows[] = $dms(2959, "OIL cleanliness - Detect and clean");
        $rows[] = $dms(3209, "Protection harnais et flexible - Harnesses and hoses protection");
        $rows[] = $dms(3218, "TMX - alignement des roues / TMX wheels alignment");
        $rows[] = $dms(3434, "Tension des cha&icirc;nes MDW. MDW chain tension.");
        $rows[] = $dms(3436, "Protection de la peinture &agrave; l'assemblage / Assembly Paint protection");
        $rows[] = $dms(3515, "Guide de d&eacute;marrage - 99-reGen");
        $rows[] = $dms(5103, "929-E - Assemblage compartiment batterie");
        $rows[] = $dms(5130, "Instrucion d'installation des &eacute;tiquettes de c&acirc;bles &eacute;lectriques");
        $rows[] = $dms(5248, "Assemblage &eacute;l&eacute;vateur 1/2");
        $rows[] = $dms(5249, "Assemblage &eacute;l&eacute;vateur 2/2");
        $rows[] = $dms(5259, "Assemblage grasshopper 838");
        $rows[] = $dms(5278, "M&eacute;thode d'assemblage - Fuel Line Deutz 2.9");
        $rows[] = $dms(5279, "M&eacute;thode d'assemblage ciseaux d'&eacute;l&eacute;vateur 838");
        $rows[] = $dms(5280, "M&eacute;thode 'flip' de plateforme loader");
        $rows[] = $dms(5283, "M&eacute;thode d'assemblage - Modules FTC");
        $rows[] = $dms(5286, "M&eacute;thode d'assemblage - Brochette MDW");
        $rows[] = $dms(5307, "M&eacute;thode d'assemblage - Man lift");
        $rows[] = $dms(5308, "M&eacute;thode d'assemblage - Ciseau &eacute;l&eacute;vateur 929");
        $rows[] = $dms(5310, "M&eacute;thode d'assemblage - Grasshoppper 929");
        $rows[] = $dms(5330, "M&eacute;thode d'assemblage - Ciseau Bridge 929");
        $rows[] = $dms(5354, "M&eacute;thode d'assemblage - Ciseau Bridge 838");
        $rows[] = $dms(5355, "M&eacute;thode d'assemblage - Ciseau &eacute;l&eacute;vateur 838");
        $rows[] = $dms(5358, "M&eacute;thode d'assemblage: flip de la plateforme 929");
        $rows[] = $dms(5359, "M&eacute;thode d'assemblage Loader Trav 125");
        $rows[] = $dms(5363, "M&eacute;thode d'assemblage: Bridge Assembly Part1 (Mk3)");
        $rows[] = $dms(5389, "M&eacute;thode d'assemblage: Bridge Assembly Part2 (Mk3)");
        $rows[] = $dms(5400, "M&eacute;thode d'assemblage: Bridge auto level sub-assembly");
        $rows[] = $dms(5401, "M&eacute;thode d'assemblage: Diesel Chassis Assembly Before Drop");
        $rows[] = $dms(5402, "M&eacute;thode d'assemblage - Chassis 929 diesel after drop");
        $rows[] = $dms(5406, "M&eacute;thode d'assemblage - TMX-50");
        $rows[] = $dms(5415, "M&eacute;thode d'assemblage - Moteur 929 CUT4F COMMINS QSF3.8");
        $rows[] = $dms(5379, "Documentation Folks Employ&eacute;");
        $rows[] = $dms(5079, "Formulaire pour rencontrer les ressources humaines");
        $rows[] = $dms(3217, "Soumettre un NCR");
        $rows[] = $dms(5079, "Ma&icirc;trise du Produit Non Conforme");
        $rows[] = $dms(5486, "Formulaire d'inspection mensuelle d'usine");
        $rows[] = $dms(508, "Cette procédure définit les responsabilités et décrit le plan d’évacuation d’urgence chez TLD Canada.");
        break;
    case '500':
        $rows[] = $dms(18, "This document is describing the quality policy for the TLD EMEAI division.");
        $rows[] = $dms(217, "C40 - BP Referentiel des bonnes pratiques en electricit&eacute;");
        $rows[] = $dms(229, "C40 - BP- Referentiel de bonnes pratique pour les montages hydraulique");
        $rows[] = $dms(230, "BP-R&eacute;f&eacute;rentiel des bonnes pratiques en fluides");
        $rows[] = $dms(231, "C40 - BP- R&eacute;f&eacute;rentiel des bonnes pratiques de montage du groupe motopropulseur et de la propulsion");
        $rows[] = $dms(232, "C40 - BP R&eacute;f&eacute;rentiel des bonnes pratiques de serrage hydraulique");
        $rows[] = $dms(1543, "Livret d'accueil sécurité pour les nouveaux arrivants à SOR (CDD, CDI, intérimaires, stagiaires).");
        $rows[] = $dms(1997, "C40 - BP R&eacute;f&eacute;rentiel des bonnes pratiques de serrage hydraulique O-Lock");
        $rows[] = $dms(2526, "TLD Group instruction for fasterners and fittings marking");
        $rows[] = $dms(814, 'Fastener Designation Code for TLD');
        $rows[] = $dms(3074, "R&egrave;gles d'acceptation d'aspect");
        $rows[] = $dms(4234, "Création du DMS du plan de circulation du site de SORIGNY");
        $rows[] = $dms(5403, "D&eacute;finition t&acirc;ches injection soft");
        $rows[] = $dms(884, "This work instruction defines the type and the method of application of greases and sealants to be used on every electrical connection.");
        $rows[] = $dms(2045, "Assembly Best Practice on Fluid, precisely on the hydraulic oil.
                                        All the important practices to guarantee that the oil is not polluted during assembly and before delivery to the customer.");
        $rows[] = $dms(7543, "Procédure pour permettre le montage des batteries CATL avec une absence de tension.");
        $rows[] = $dms(6356, "Procédure pour permettre le montage des batteries IBS avec une absence de tension.");
        $rows[] = $dms(556, "Initier une NCR");
        $rows[] = $dms(2458, "sertissage et connection MATE-N-LOK");
        $rows[] = $dms(8032, "Factory Electrical Dos and Donts");
        $rows[] = $dms(296, " specify the treatments and protections of surfaces required by TLD and the associated control. ");
        break;
    case '640':
        $rows[] = $dms(1421, "&#32489;&#25928;&#35780;&#20215;&#31649;&#29702;&#21046;&#24230; Appraisal Management System");
        $rows[] = $dms(1656, "&#20316;&#19994;&#25351;&#23548;&#20070;&#31649;&#29702;&#21150;&#27861; WI-SHA-M-012-A");
        $rows[] = $dms(1919, "&#28082;&#21387;&#31995;&#32479;&#25509;&#22836;&#31649;&#34746;&#32441;&#23494;&#23553;&#33014;&#20351;&#29992;&#25351;&#23548; Instruction of thread sealant implant on hydraulic system.");
        $rows[] = $dms(2043, "&#28082;&#21387;&#31649;&#36335;&#38;&#25509;&#22836;&#38450;&#25252;&#25351;&#23548; Instruction of protection on hydraulic pipes &fittings");
        $rows[] = $dms(2045, "Assembly Best Practice on Fluids");
        $rows[] = $dms(2113, "&#28082;&#21387;&#31995;&#32479;&#31649;&#36335;&#25509;&#22836;&#25319;&#32039;&#25197;&#30697;&#25351;&#24341; Instruction of torque on hydraulic system");
        $rows[] = $dms(2183, "&#28938;&#25509;&#25351;&#23548; Instruction of Welding");
        $rows[] = $dms(2195, "GPU&#30005;&#22120;&#23433;&#35013;&#25351;&#23548;");
        $rows[] = $dms(2196, "NBL&#30005;&#22120;&#23433;&#35013;&#25351;&#23548;");
        $rows[] = $dms(2197, "ABS&#30005;&#22120;&#23433;&#35013;&#25351;&#23548;");
        $rows[] = $dms(2312, "&#32039;&#22266;&#20214;&#38450;&#26494;&#26631;&#35760;&#25351;&#23548; Instruction of fastener marking");
        $rows[] = $dms(2328, "GPU&#30005;&#32518;&#23433;&#35013;&#25351;&#23548;");
        $rows[] = $dms(2388, "&#39550;&#39542;&#23460;&#39030;&#35686;&#28783;&#12289;&#24037;&#20316;&#28783;&#31561;&#25171;&#30789;&#33014;&#23494;&#23553;&#25351;&#23548; Instruction of silicone implants on cab roof beacon,etc");
        $rows[] = $dms(2449, "&#38646;&#37096;&#20214;&#34920;&#38754;&#38450;&#25252;&#25351;&#23548; DMS 2022 - Surface protection at assembly");
        $rows[] = $dms(2489, "&#23433;&#20840;&#29983;&#20135; Safety in production");
        $rows[] = $dms(2532, "&#35843;&#36895;&#38400;&#32039;&#22266;&#25351;&#23548; Instruction of fastening flow control valve");
        $rows[] = $dms(2538, "&#29123;&#27833;&#31665;&#28082;&#20301;&#20256;&#24863;&#22120;&#31649;&#36335;&#36830;&#25509;&#25351;&#23548; Instruction of hose connection for fuel level sensor on fuel tank");
        $rows[] = $dms(2539, "&#39135;&#21697;&#36710;&#21098;&#20992;&#26550;&#38144;&#36724;&#23433;&#35013;&#25351;&#23548; Instruction of assembly scissors top pivot pins for catering truck");
        $rows[] = $dms(2579, "ABS&#36873;&#35013;&#36739;&#22810;&#26102;PCB&#30005;&#36335;&#26495;&#22788;&#30005;&#27668;&#24067;&#32447;&#25351;&#23548; Best practice layout for PCB plate electric harness for ABS(not include E version) with lots electric options");
        $rows[] = $dms(2664, "Employee Incentive and Retention Bonus System");
        $rows[] = $dms(2705, "&#28082;&#21387;&#31649;&#36335;&#23433;&#35013;&#25351;&#21335; Hydraulic best practice Guidelines");
        $rows[] = $dms(1219, "&#24037;&#21378;&#28165;&#27905;&#25972;&#29702;&#26816;&#26597;&#35268;&#23450; Housekeeping and cleanliness");
        $rows[] = $dms(2664, "&#21592;&#24037;&#28608;&#21169;&#21450;&#22870;&#37329;&#21046;&#24230; Employee Incentive and Retention Bonus System");
        $rows[] = $dms(2448, "&#35774;&#22791;&#25805;&#20316;&#35268;&#31243; Equipment regulation");
        $rows[] = $dms(2216, "&#20581;&#24247;&#21644;&#23433;&#20840;&#35268;&#31243; EHS Procedure");
        $rows[] = $dms(2127, "PIO&#32593;&#19978;&#26816;&#39564;&#31995;&#32479;&#22521;&#35757;&#25351;&#23548;  PIO training for CQ managers");
        $rows[] = $dms(2128, "CRAB&#27969;&#31243; CRAB Procedure and user guide");
        break;
    case '520':
        $rows[] = $dms(217, "Referentiel des bonnes pratiques en electricité");
        $rows[] = $dms(229, "C40 - BP- Referentiel de bonnes pratique pour les montages hydraulique");
        $rows[] = $dms(2458, "sertissage et connection MATE-N-LOK");
        $rows[] = $dms(2526, "TLD Group instruction for fasterners and fittings marking");
        $rows[] = $dms(6180, "Formation utilisation de la tablette");
        $rows[] = $dms(6197, "D&eacute;chiffrer la repr&eacute;sentation symbolique");
        $rows[] = $dms(6224, "Crit&egrave;re d'acceptation de sertissage des contacts");
        $rows[] = $dms(884, "ELECTRICAL ASSEMBLY TECHNIQUES");
        $rows[] = $dms(6243, "Savoir lire un sch&eacute;ma &eacute;lectrique");
        $rows[] = $dms(6285, "Cheminement des faisceaux");
        $rows[] = $dms(556, "Initier une NCR");
        $rows[] = $dms(5596, "Suivi pièce FAQ 1er montage");
        $rows[] = $dms(6008, "Enregistrement photos dans files ER");
        $rows[] = $dms(6382, "Méthode de diagnostic de pannes avec l'afficheur CR0452");
        $rows[] = $dms(6383, "Contacts et pinces à sertir associés");
        $rows[] = $dms(6424, "Réseau CAN");
        $rows[] = $dms(6477, "Procédure de mise en route d'un véhicule thermique");
        $rows[] = $dms(6478, "Procédure de mise en route d'un JET16 sur vireur");
        $rows[] = $dms(6537, "Paramétrage et réglage des capteurs vision 3D");
        $rows[] = $dms(6568, "Procédure de mise à jour des Software du BMS");
        $rows[] = $dms(6569, "Procédure de vérifications avec chargeur à bord monophasé");
        $rows[] = $dms(6786, "Procédure de fonctionnement de l'outil de diagnostic Perkins");
        $rows[] = $dms(7017, "Procédure de mise en route d'un véhicule hybride");
        $rows[] = $dms(814, 'Fastener Designation Code for TLD');
        $rows[] = $dms(232, 'C40 - BP Référentiel des bonnes pratiques de serrage hydraulique');
        $rows[] = $dms(7398, "Procédure de paramétrage d'un boitier Astus");
        $rows[] = $dms(230, "BP - Référentiel des bonnes pratiques en fluides. ");
        $rows[] = $dms(5605, "Job description – System & Network Administrator");
        $rows[] = $dms(2060, "Bonnes pratiques interventions 'PERCAGE' sur véhicule");
        $rows[] = $dms(7957, "Bonnes pratiques de levage");
        $rows[] = $dms(8032, "Factory Electrical Dos and Donts");
        $rows[] = $dms(231, "C40 - BP- Référentiel des bonnes pratiques de montage du groupe motopropulseur et de la propulsion.");
        $rows[] = $dms(296, " specify the treatments and protections of surfaces required by TLD and the associated control. ");
        $rows[] = $dms(2045, "Assembly Best Practice on Fluid, precisely on the hydraulic oil.");
        $rows[] = $dms(1555, " Livret d'accueil HSE pour les nouveaux arrivants à STL (CDD, CDI, intérimaires, stagiaires). ");
        $rows[] = $dms(8152, "Formation sur les bonnes pratiques de montage hydraulique");
        $rows[] = $dms(8454, "HYDRAULIC PIPING TRIPLE-LOK ");
        break;
    case '400':
    case '410':
        $rows[] = $dms(1093, 'Emergency Action Plan');
        $rows[] = $dms(1094, 'Bloodborne Pathogen Program');
        $rows[] = $dms(3126, 'PPE Policy ');
        $rows[] = $dms(1096, 'Respiratory Protection Program');
        $rows[] = $dms(1089, 'Hearing Conservation ');
        $rows[] = $dms(1090, 'Lockout Tagout Program ');
        $rows[] = $dms(1053, 'Hot Work Permit Program');
        $rows[] = $dms(1098, 'Electrical Safety');
        $rows[] = $dms(3043, 'Fall Protection Policy ');
        $rows[] = $dms(1097, 'Powered Industrial Truck Program ');
        $rows[] = $dms(1101, 'Tool Safety ');
        $rows[] = $dms(559, 'Accident Investigation Procedure ');
        $rows[] = $dms(3141, 'Modified Duty Program');
        $rows[] = $dms(1088, 'Hazard Communication Program');
        $rows[] = $dms(2934, 'Fire Safety Map');
        $rows[] = $dms(1380, 'Safety Orientation');
        $rows[] = $dms(2919, 'Isocyanate Safety Training');
        $rows[] = $dms(3006, 'JHA');
        $rows[] = $dms(3065, 'LOTO Authorized Personnel Training');
        $rows[] = $dms(3094, 'Powerwashing units');
        $rows[] = $dms(1187, 'ACU 802 LOTO Procedure ');
        $rows[] = $dms(1188, 'ACU 804 LOTO Procedure');
        $rows[] = $dms(1189, 'ACU 804 MIL LOTO Procedure ');
        $rows[] = $dms(1190, 'ACU 808 LOTO Procedure ');
        $rows[] = $dms(1191, 'GPU LOTO Procedure ');
        $rows[] = $dms(1192, 'ASU 500 series  LOTO Procedure ');
        $rows[] = $dms(1193, 'LCU-25-CUP LOTO Procedure ');
        $rows[] = $dms(1194, 'LCU-25-EMP LOTO Procedure ');
        $rows[] = $dms(897, 'TLD ACE Quality Policy ');
        break;
}
$report = new tldReportColumnar(
    $rows,
    [
        "xItems" => [
            "id" => _("ID#"),
            "desc" => "Description",
        ],
        "title" => _("DMS - Shopfloor"),
        "links" => [
            "id" => "$SHOPFLOOR_URL/index.php?m[0]=dms&id="
        ]
    ]
);
$body = $report->fetch();
?>
