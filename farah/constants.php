<?php // © 2017 Daniel Schulz

const NAMESPACE_SEPARATOR = '\\';

const DIR_PHP 			= 'php/';
const DIR_PHP_LIB		= 'php-lib/';
const DIR_THUMBNAILS 	= 'cache/images/';

const DATE_DATETIME = 'd.m.y H:i:s';
const DATE_DATE = 'd.m.y';
const DATE_TIME = 'H:i:s';
const DATE_UTC = DATE_W3C;

const TIME_USLEEP_FACTOR = 1000000;
const TIME_MILLISECOND = 0.001;
const TIME_SECOND = 1;
const TIME_MINUTE = 60;
const TIME_HOUR = 3600;
const TIME_DAY = 86400;
const TIME_WEEK = 604800;
const TIME_MONTH = 2628000;
const TIME_YEAR = 31536000;
const TIME_DECADE = 315360000;

const MEMORY_BYTE = 1;
const MEMORY_KILOBYTE = 1024;
const MEMORY_MEGABYTE = 1048576;
const MEMORY_GIGABYTE = 1073741824;

const CHAR_ZEROWIDTHSPACE = '​';
const BCP47_PREGMATCH = '/(?<language>[a-z]{2,3})(?:-(?<extlang>aao|abh|abv|acm|acq|acw|acx|acy|adf|ads|aeb|aec|aed|aen|afb|afg|ajp|apc|apd|arb|arq|ars|ary|arz|ase|asf|asp|asq|asw|auz|avl|ayh|ayl|ayn|ayp|bbz|bfi|bfk|bjn|bog|bqn|bqy|btj|bve|bvl|bvu|bzs|cdo|cds|cjy|cmn|coa|cpx|csc|csd|cse|csf|csg|csl|csn|csq|csr|czh|czo|doq|dse|dsl|dup|ecs|esl|esn|eso|eth|fcs|fse|fsl|fss|gan|gds|gom|gse|gsg|gsm|gss|gus|hab|haf|hak|hds|hji|hks|hos|hps|hsh|hsl|hsn|icl|ils|inl|ins|ise|isg|isr|jak|jax|jcs|jhs|jls|jos|jsl|jus|kgi|knn|kvb|kvk|kvr|kxd|lbs|lce|lcf|liw|lls|lsg|lsl|lso|lsp|lst|lsy|ltg|lvs|lzh|max|mdl|meo|mfa|mfb|mfs|min|mnp|mqg|mre|msd|msi|msr|mui|mzc|mzg|mzy|nan|nbs|ncs|nsi|nsl|nsp|nsr|nzs|okl|orn|ors|pel|pga|pks|prl|prz|psc|psd|pse|psg|psl|pso|psp|psr|pys|rms|rsi|rsl|sdl|sfb|sfs|sgg|sgx|shu|slf|sls|sqk|sqs|ssh|ssp|ssr|svk|swc|swh|swl|syy|tmw|tse|tsm|tsq|tss|tsy|tza|ugn|ugy|ukl|uks|urk|uzn|uzs|vgt|vkk|vkt|vsi|vsl|vsv|wuu|xki|xml|xmm|xms|yds|ysl|yue|zib|zlm|zmi|zsl|zsm))?(?:-(?<script>afak|aghb|ahom|arab|armi|armn|avst|bali|bamu|bass|batk|beng|blis|bopo|brah|brai|bugi|buhd|cakm|cans|cari|cham|cher|cirt|copt|cprt|cyrl|cyrs|deva|dsrt|dupl|egyd|egyh|egyp|elba|ethi|geok|geor|glag|goth|gran|grek|gujr|guru|hang|hani|hano|hans|hant|hatr|hebr|hira|hluw|hmng|hrkt|hung|inds|ital|java|jpan|jurc|kali|kana|khar|khmr|khoj|knda|kore|kpel|kthi|lana|laoo|latf|latg|latn|lepc|limb|lina|linb|lisu|loma|lyci|lydi|mahj|mand|mani|maya|mend|merc|mero|mlym|modi|mong|moon|mroo|mtei|mult|mymr|narb|nbat|nkgb|nkoo|nshu|ogam|olck|orkh|orya|osma|palm|pauc|perm|phag|phli|phlp|phlv|phnx|plrd|prti|rjng|roro|runr|samr|sara|sarb|saur|sgnw|shaw|shrd|sidd|sind|sinh|sora|sund|sylo|syrc|syre|syrj|syrn|tagb|takr|tale|talu|taml|tang|tavt|telu|teng|tfng|tglg|thaa|thai|tibt|tirh|ugar|vaii|visp|wara|wole|xpeo|xsux|yiii|zinh|zmth|zsym|zxxx|zyyy|zzzz))?(?:-(?<region>ac|ad|ae|af|ag|ai|al|am|an|ao|aq|ar|as|at|au|aw|ax|az|ba|bb|bd|be|bf|bg|bh|bi|bj|bl|bm|bn|bo|bq|br|bs|bt|bu|bv|bw|by|bz|ca|cc|cd|cf|cg|ch|ci|ck|cl|cm|cn|co|cp|cr|cs|cu|cv|cw|cx|cy|cz|dd|de|dg|dj|dk|dm|do|dz|ea|ec|ee|eg|eh|er|es|et|eu|fi|fj|fk|fm|fo|fr|fx|ga|gb|gd|ge|gf|gg|gh|gi|gl|gm|gn|gp|gq|gr|gs|gt|gu|gw|gy|hk|hm|hn|hr|ht|hu|ic|id|ie|il|im|in|io|iq|ir|is|it|je|jm|jo|jp|ke|kg|kh|ki|km|kn|kp|kr|kw|ky|kz|la|lb|lc|li|lk|lr|ls|lt|lu|lv|ly|ma|mc|md|me|mf|mg|mh|mk|ml|mm|mn|mo|mp|mq|mr|ms|mt|mu|mv|mw|mx|my|mz|na|nc|ne|nf|ng|ni|nl|no|np|nr|nt|nu|nz|om|pa|pe|pf|pg|ph|pk|pl|pm|pn|pr|ps|pt|pw|py|qa|re|ro|rs|ru|rw|sa|sb|sc|sd|se|sg|sh|si|sj|sk|sl|sm|sn|so|sr|ss|st|su|sv|sx|sy|sz|ta|tc|td|tf|tg|th|tj|tk|tl|tm|tn|to|tp|tr|tt|tv|tw|tz|ua|ug|um|us|uy|uz|va|vc|ve|vg|vi|vn|vu|wf|ws|yd|ye|yt|yu|za|zm|zr|zw|001|002|003|005|009|011|013|014|015|017|018|019|021|029|030|034|035|039|053|054|057|061|142|143|145|150|151|154|155|419))?(?:-(?<variant>1606nict|1694acad|1901|1959acad|1994|1996|alalc97|aluku|arevela|arevmda|baku1926|barla|bauddha|biscayan|biske|bohoric|boont|dajnko|ekavsk|emodeng|fonipa|fonupa|fonxsamp|hepburn|heploc|hognorsk|ijekavsk|itihasa|jauer|jyutping|kkcor|kscor|laukika|lipaw|luna1918|metelko|monoton|ndyuka|nedis|njiva|nulik|osojs|pamaka|petr1708|pinyin|polyton|puter|rigik|rozaj|rumgr|scotland|scouse|solba|sotav|surmiran|sursilv|sutsilv|tarask|uccor|ucrcor|ulster|unifon|vaidika|valencia|vallader|wadegile))?/';

const CORE_XSLT_PHP         = 'php';
const CORE_XSLT_LIBXML      = 'libxml';
const CORE_XSLT_EXSELT      = 'exselt';
const CORE_XSLT_SAXON8      = 'saxon8';
const CORE_XSLT_SAXON9      = 'saxon9';
const CORE_XSLT_SAXONC      = 'saxonc';

//side effects:
require __DIR__ . DIRECTORY_SEPARATOR . 'config.php';
require __DIR__ . DIRECTORY_SEPARATOR . 'functions.php';
require __DIR__ . DIRECTORY_SEPARATOR . sprintf('constants-%s.php', PHP_SAPI);

chdir(SERVER_ROOT);

require 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

stream_wrapper_register('farah', 'Slothsoft\Farah\Stream\FarahWrapper');

set_error_handler(
    function (int $errno, string $errstr, string $errfile, int $errline ) {
        throw new ErrorException($errstr, $errno, $errno, $errfile, $errline);
    },
    E_ALL
);

ini_set('mysqli.default_user', DB_USER);
ini_set('mysqli.default_pw', DB_PASSWORD);

if (isset($_REQUEST['tick'])) {
	//declare(ticks = 1000); //put this into to-be-observed file
	register_tick_function(['\\Slothsoft\\Farah\\Tracking\\Tick', 'log']);
}

\Slothsoft\Farah\HTTPRequest::prepareEnvironment($_SERVER);