/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

DROP DATABASE IF EXISTS `portal`;
CREATE DATABASE IF NOT EXISTS `portal` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;
USE `portal`;

DROP TABLE IF EXISTS `clanci`;
CREATE TABLE IF NOT EXISTS `clanci` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `naslov` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `clanak` text COLLATE utf8mb4_unicode_ci,
  `rezime` tinytext COLLATE utf8mb4_unicode_ci,
  `objavljen` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `pregledi` int(8) unsigned NOT NULL DEFAULT '0',
  `korisnik_id` int(10) unsigned NOT NULL,
  `kategorija_id` int(10) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `published_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_clanci_kategorije` (`kategorija_id`),
  CONSTRAINT `FK_clanci_kategorije` FOREIGN KEY (`kategorija_id`) REFERENCES `kategorije` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DELETE FROM `clanci`;
/*!40000 ALTER TABLE `clanci` DISABLE KEYS */;
INSERT INTO `clanci` (`id`, `naslov`, `clanak`, `rezime`, `objavljen`, `pregledi`, `korisnik_id`, `kategorija_id`, `created_at`, `updated_at`, `published_at`, `deleted_at`) VALUES
	(1, 'Бечић изабран за председника Скупштине Црне Горе', 'ПОДГОРИЦА - Лидер коалиције „Мир је наша нација” и председник Демократске Црне Горе Алекса Бечић изабран је за председника Скупштине Црне Горе. За његово именовање гласало је 45 посланика, а против је био један посланик, док је 25 листића било празно.\r\n\r\n„Неописива је част и привилегија бити изабран за председника највишег законодавног и представничког дома, а истовремено бити и први изабрани јавни функционер након прве демократске смене власти у црногорској историји. Они који ме нису гласали да знају да ћу се за њих још више борити, да су им моја врата широм отворена. Нећу их нападати, критиковати или проглашавати противницима државе. Хоћу да опозиција има бољи статус него што смо га ми имали”, поручио је Бечић након избора.\r\n\r\nПре гласања посланици коалиција „За будућност Црне Горе и „Црно на бијело”, посланици Бошњачке странке и посланик Албанске изборне листе „Генци Ниманбегу-Ник Гјелосхај” Ник Ђељошај, најавили су да ће подржати предлог да Бечић буде председник Скупштине.\r\n\r\nДПС и СДП најавили су да ће бити „уздржани”, док су Андрија Поповић (Либерална партија) и Социјалдемократе Црне Горе казали да ће бити против избора Бечића за председника парламента. Посланик ДПС-а Милош Николић казао је да та партија поводом предлога да Бечић буде председник Скупштине „ДПС имати неутралан став”.\r\n\r\n„Нећемо гласати ни за Бечића, али ни против”, рекао је Николић. Посланик Либералне партије Андрија Поповић казао је да постоји бојазан да ће доћи „до дезинтеграције Црне Горе, губитка нашег суверинитета”.\r\n\r\nДржавна избора комисија Црне Горе (ДИК) је објавила 14. септембра коначне резултате парламентарних избора, према којима су три опозициона савеза освојила 41 посланичко место док актуелна владајућа већина има 40 места у црногорском парламенту.\r\n\r\nПрема коначним изборним резултатима, коалиција „Одлучно - Демократска партија социјалиста - Мило Ђукановић” освојила је 30 мандата од којих један припада Либералној партији која је била дио коалиције.\r\n\r\nОпозициона коалиција „За будућност Црне Горе” освојила је 27 мандата, од чега 18 припада Демократском фронту који чине Нова српска демократија, Демократска народна партија, Покрет за промјене и Демократска народна партија. Као чланице савеза „За будућност Црне Горе” Социјалистичка народна партија (СНП) имаће пет посланика, Уједињена Црна Гора једног, а Радничка партија и Права Црна Гора по једног представника. Носилац листе Здравко Кривокапић је тај опозициони савез предводио као нестраначка личност.\r\n\r\nОпозициона коалиција „Мир је наша нација” освојила је 10 посланичких места, од чега девет припада Демократској Црној Гори, а један Демосу. Опозициони савез „Црно на бијело” има четири посланика која припадају грађанском покрету УРА.\r\n\r\nБошњачка странка и Социјалдемократе имаће по три посланика, а Социјалдемократска партија два. У црногорском парламенту по једног представника имаће Албанска листа „Сада је вријеме” и коалиција „Једногласно”, јавља Бета.', 'Мир је наша нација', 1, 10, 1, 1, '2020-09-23 19:30:26', '2020-09-23 19:30:27', '2020-09-23 19:30:29', NULL),
	(2, 'КФОР представио италијански медицински тим за кови', 'Италијански војно-медицински тим који ће наредних недеља бити активан на читавој територији Косова и Метохије као подршка у одговору институција на ковид19 представљен је данас у Приштини, саопштио је КФОР.\r\n\r\nТим су обезбедили војска, морнарица, ваздухопловне снаге и Карабињери, а представљању су присуствовали косовски премијер Авдулах Хоти, амбасадор Италије у Приштини Никола Орландо, који је промовисао билатералну цивилно-војну иницијативу и генерал Микеле Риси , шеф КФОР мисије НАТО-а, који ће олакшати рад тима.\r\n\r\nДолазак тима резултат је италијанске националне иницијативе Министарства одбране и Министарства спољних послова те земље.\r\n\r\nТим чине доктори специјалисти и медицинске сестре, а предводи га мајор Кристијан Вито Бенеђиамо који - као и остали чланови тима - има искуство рада на првој линији фронта против ковида у разним болницама у северној Италији током раних фаза ванредне ситуације.\r\n\r\nИталијанска агенција за развојну сарадњу, која је активна неколико година у подршци локалном здравственом систему, обезбеђује везу тима са косовским здравственим институцијама.\r\n\r\nТим је на Косово стигао почетком септембра а до сада је био ангажован на низу састанака са здравственим особљем у главним косовским болницама, како би израдио и ојачао ковид19 методе за управљање ванредним ситуацијама на целој територији у складу са међународним инстанцама.\r\n\r\nКако се наводи тим ће допринети дефинисању интервентних стратегија, у сусрет зимској сезони и другом таласу инфекције.\r\n\r\n„Овом артикулисаном интервенцијом у знак подршке косовским здравственим властима, Италија се афирмисала као примарни партнер у борби против коронавируса заједно са институцијама и грађанима ове земље”, рекао је амбасадор Орландо.\r\n\r\nДодао је да је долазак тима нови пример цивилно-војне сарадње који потврђује италијанску националну посвећеност Косову.\r\n\r\n„Позивам све грађане Косова да и даље стриктно поштују једноставна правила која побеђују вирус: Ставите маске, држите растојање и редовно перите руке. Заједно ћемо успети!”, апеловао је амбасадор.\r\n\r\nКомандант КФОР-а Риси нагласио је допринос мисије НАТО-а на Косову у овој области. „КФОР олакшава рад војно-медицинског тима као што је и био случај последњих месеци са специјализованим тимом италијанске војске који је обновио многе јавне зграде широм Косова”, рекао је он.\r\n\r\nВођа италијанског војно-медицинског тима Бенеђиамо нагласио је значај превенције у борби против ковид19, и истакао значај климе професионалне сарадње која је већ успостављена са лекарима главних болница на Косову.\r\n\r\nОд избијања вируса на Косову, Италија је била на првој линији пружања помоћи локалним властима, наводи се у саопштењу које преноси Танјуг, и додај да је италијански војни контингент КФОР-а недавно обновио две школе.\r\n\r\nДодаје се да је Италијанска агенција за међународну сарадњу ојачала је национални здравствени систем испоруком комплетног одељења за кардиохирургију, специјализованом обуком лекара и медицинских сестара, а недавно је предала лабораторију за вирусне анализе и значајне количине личне и медицинске заштитне опреме.', 'КФОР', 1, 0, 1, 2, '2020-09-22 19:31:43', '2020-09-22 19:31:47', '2020-09-22 19:31:50', NULL),
	(3, 'Представа „Чипка” је прича о хуманости', 'Aнсамбл Ријечког балета je изнедрио специфичне особености, а оне су окосница мог тренутног ауторског пројекта „Чипка” који ћете видети у Београду. Публици се допада наш тврдоглав покушај да се кроз кореографска дела надовезујемо на раније радове, на нов начин. У „Чипки” која евоцира уметност преживљавања, публици на БФИ желимо пренети оно што нас тренутно заокупља, а то је начин како један балетски ансамбл ове регије може преживети, кроз играчко заједништво, хуманост и солидарност, рекла је за „Политику” Маша Колар, кореограф и директорка балета ХНК Ивана пл. Зајца из Ријеке, поводом њиховог извођења комада „Чипка” на 17. Београдском фестивалу игре, вечерас у 20 часова у Мадлениануму и сутра увече у исто време у СНП-у у Новом Саду.', 'Чипка', 1, 7, 1, 14, '2020-09-18 18:32:53', '2020-09-23 18:32:56', '2020-09-18 18:32:58', NULL),
	(4, 'Протест у Минску после инаугурације Лукашенка', 'МИНСК - Неколико особа је повређено када је полиција употребила водене топове током протеста у Минску, док је више од 10 особа задржано у притвору, преноси Ројтерс позивајући се на руске медије.\r\n\r\nПрема наводима очевица британске агенције, хиљаде људи изашло је на улице Минска како би протестовали против изненадне инаугурације председника Белорусије Александра Лукашенка. Демонстранти су носили заставе опозиције у црвеној и белој боји, док су аутомобили у пролазу свирали у знак солидарности.\r\n\r\nНа једом од транспарената који су учесници протеста носили писало је: „Ако имаш 80 одсто, зашто нас се плашис?”, чиме се алудира на Лукашенкове наводе да је на председничким изборима 9. августа освојио 80 одсто гласова.\r\n\r\nРуска агенција Интерфакс објавила је да је неколико особа повређено када су употребљени водени топови, док је агенција РИА навела да је више од 10 особа задржано у притвору. Демонстранти су раније данас почели да се окупљају у малим групама у Минску, укључујући испред најмање три унивезитета, показују фотографије локалних медија.\r\n\r\nНеки су узвикивали: „Саша (надимак за Лукашенка), изађи, честитаћемо ти!" Опозициони политицар Павел Латушко рекао је да је полагање заклетве било као „тајни састанак лопова”.\r\n\r\n„Где су усхићени грађани? Где је дипломатски кор? Очигледно је да је Александар Лукашенко искључиво председник ОМОН-а (специјалних полицијских снага) и шачице лажљивих званичника”, навео је он на друштвеним медијима.', 'Минск', 1, 0, 1, 7, '2020-08-15 20:23:32', '2020-08-15 20:23:50', '2020-08-15 20:23:59', '2020-09-24 08:06:43'),
	(5, 'Хоти и Боулер потписали економски споразум', 'ПРИШТИНА - Премијер привремених косовских институција Авдулах Хоти и извршни директор Међународне развојне финансијске корпорације (ДФЦ) Адам Болер потписали су споразум о сарадњи.', 'Хоти и Боулер', 1, 0, 1, 1, '2019-09-23 20:25:21', '2019-09-23 20:25:25', '2019-09-23 20:25:27', NULL);
/*!40000 ALTER TABLE `clanci` ENABLE KEYS */;

DROP TABLE IF EXISTS `dokumenti`;
CREATE TABLE IF NOT EXISTS `dokumenti` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `clanak_id` int(10) unsigned DEFAULT NULL,
  `link` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `opis` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `vrsta` enum('закони','уредбе','правилници','обрасци','линкови') COLLATE utf8mb4_unicode_ci NOT NULL,
  `korisnik_id` int(10) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `FK_dokumenti_korisnici` (`korisnik_id`),
  KEY `FK_dokumenti_clanci` (`clanak_id`),
  CONSTRAINT `FK_dokumenti_clanci` FOREIGN KEY (`clanak_id`) REFERENCES `clanci` (`id`),
  CONSTRAINT `FK_dokumenti_korisnici` FOREIGN KEY (`korisnik_id`) REFERENCES `korisnici` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DELETE FROM `dokumenti`;
/*!40000 ALTER TABLE `dokumenti` DISABLE KEYS */;
/*!40000 ALTER TABLE `dokumenti` ENABLE KEYS */;

DROP TABLE IF EXISTS `kategorije`;
CREATE TABLE IF NOT EXISTS `kategorije` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `naziv` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `vrsta` enum('опште','управе','синдикати') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DELETE FROM `kategorije`;
/*!40000 ALTER TABLE `kategorije` DISABLE KEYS */;
INSERT INTO `kategorije` (`id`, `naziv`, `vrsta`) VALUES
	(1, 'Вести', 'опште'),
	(2, 'Саопштења', 'опште'),
	(3, 'Адресар ЈП и установа', 'опште'),
	(4, 'Градска управа за послове органа Града', 'управе'),
	(5, 'Градска управа за јавне приходе и инспекцијске послове', 'управе'),
	(6, 'Градска управа за развој', 'управе'),
	(7, 'Градска управа за друштвене делатности и послове са грађанима', 'управе'),
	(8, 'Градска управа за заједничке послове', 'управе'),
	(9, 'Градско правобранилаштво', 'управе'),
	(10, 'Служба за интерну ревизију', 'управе'),
	(11, 'Служба за буџетску инспекцију', 'управе'),
	(12, 'Служба заштитника грађана', 'управе'),
	(13, 'Самостални синдикат', 'синдикати'),
	(14, 'Синдикат "Независност"', 'синдикати'),
	(15, 'Синдикат "Пера"', 'синдикати');
/*!40000 ALTER TABLE `kategorije` ENABLE KEYS */;

DROP TABLE IF EXISTS `komentari`;
CREATE TABLE IF NOT EXISTS `komentari` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_clanka` int(10) unsigned NOT NULL,
  `naslov` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `korisnik` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `korisnik_ip` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `komentar` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `published_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_komentari_clanci` (`id_clanka`),
  CONSTRAINT `FK_komentari_clanci` FOREIGN KEY (`id_clanka`) REFERENCES `clanci` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DELETE FROM `komentari`;
/*!40000 ALTER TABLE `komentari` DISABLE KEYS */;
/*!40000 ALTER TABLE `komentari` ENABLE KEYS */;

DROP TABLE IF EXISTS `korisnici`;
CREATE TABLE IF NOT EXISTS `korisnici` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ime` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `prezime` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `korisnicko_ime` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lozinka` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dozvoljene_kategorije` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nivo` int(10) unsigned NOT NULL,
  `korisnik_id` int(10) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`korisnicko_ime`),
  KEY `FK_korisnici_korisnici` (`korisnik_id`),
  CONSTRAINT `FK_korisnici_korisnici` FOREIGN KEY (`korisnik_id`) REFERENCES `korisnici` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DELETE FROM `korisnici`;
/*!40000 ALTER TABLE `korisnici` DISABLE KEYS */;
INSERT INTO `korisnici` (`id`, `ime`, `prezime`, `email`, `korisnicko_ime`, `lozinka`, `dozvoljene_kategorije`, `nivo`, `korisnik_id`, `created_at`) VALUES
	(1, 'Admin', '', '', 'admin', '$2y$10$RWD9bVOhe1GlWER7DVKMAukc2/OAwpoAvC/8A.wYOpGtqMFTezQHm', '', 0, 1, '2020-01-08 19:47:03');
/*!40000 ALTER TABLE `korisnici` ENABLE KEYS */;

DROP TABLE IF EXISTS `logovi`;
CREATE TABLE IF NOT EXISTS `logovi` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `opis` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '0',
  `datum` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `izmene` text COLLATE utf8mb4_unicode_ci,
  `tip` enum('brisanje','dodavanje','izmena','upload') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'dodavanje',
  `korisnik_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_logovi_korisnici` (`korisnik_id`),
  CONSTRAINT `FK_logovi_korisnici` FOREIGN KEY (`korisnik_id`) REFERENCES `korisnici` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DELETE FROM `logovi`;
/*!40000 ALTER TABLE `logovi` DISABLE KEYS */;
/*!40000 ALTER TABLE `logovi` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
