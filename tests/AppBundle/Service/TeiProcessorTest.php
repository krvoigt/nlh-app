<?php

namespace tests\AppBundle\Service;

use AppBundle\Service\TeiProcessor;

class TeiProcessorTest extends \PHPUnit_Framework_TestCase
{
     public function teiDataProvider()
    {
        return [
            [
                '
<?xml version="1.0" encoding="UTF-8"?>
<TEI.2><teiHeader/><text><body>
      <p id="IDe">
        <w function="511,80,740,165">SSerlog</w> <w function="789,93,907,146">öon</w>  <w function="974,77,1273,145">Danbent</w><w function="1273,100,1287,162">)</w><w function="1295,79,1419,145">oecî</w> <w function="1478,82,1535,150">&amp;</w> <w function="1592,79,1904,161">ÎJupredjt</w>  <w function="1974,78,2036,145">itt</w> <w function="2088,78,2423,163">©ottingen</w><w function="2430,132,2440,144">.</w> </p>
      <p id="IDf">
        <w function="347,274,442,337">3m</w> <w function="481,273,696,325">October</w> <w function="735,272,1001,338">ег|фешеп</w> <w function="1040,272,1200,335">fcrnei</w> </p>
      <p id="ID10">
        <w function="164,398,210,465">J</w>
        <w function="220,445,238,464">,</w> <w function="288,396,760,480">Meînhold^</w> <w function="812,394,966,456">Prof</w><w function="963,445,974,456">.</w> <w function="1026,395,1150,455">Lie</w><w function="1171,444,1184,472">,</w>  <w function="1255,392,1366,460">Die</w> <w function="1415,393,2088,480">Jesajaerzählungen</w> <w function="2140,401,2163,473">(</w><w function="2169,394,2394,480">lesaja</w> <w function="2442,397,2713,464">36—39</w><w function="2722,396,2754,482">)</w><w function="2765,450,2779,463">.</w> </p>
      <p id="ID11">
        <w function="340,499,505,562">Eine</w> <w function="552,498,883,562">liistorisch</w><w function="891,537,910,544">-</w><w function="917,498,1210,560">kritiM\'lu\'</w> <w function="1262,498,1756,573">riitersuchimu^</w><w function="1763,549,1772,560">.</w>                                 <w function="2468,501,2506,560">3</w> <w function="2558,499,2677,562">Mk</w><w function="2684,551,2694,562">.</w> </p>
      <p id="ID12">
        <w function="164,639,231,705">M</w>
        <w function="240,687,259,708">.</w> <w function="309,638,566,704">Fried</w> <w function="587,638,888,704">lander</w><w function="904,687,924,720">,</w>  <w function="998,636,1122,703">Der</w> <w function="1170,635,1668,703">vorchristliche</w> <w function="1715,635,2014,722">jüdische</w> <w function="2066,636,2539,706">Gnosticismus</w><w function="2548,691,2560,704">.</w> </p>
      <p id="ID13">
        <w function="2009,741,2182,803">Pi44s</w> <w function="2230,749,2383,804">etwa</w>  <w function="2440,743,2554,804">Mk</w><w function="2562,794,2572,805">.</w> <w function="2621,745,2660,806">2</w><w function="2667,794,2678,805">.</w><w function="2684,750,2766,803">S0</w><w function="2771,793,2782,805">.</w> </p>
      <p id="ID14">
        <w function="179,971,292,1058">1л</w> <w function="311,1018,345,1058">1</w> <w function="363,1022,392,1058">/</w><w function="392,970,535,1058">Nt&gt;</w>    <w function="650,967,835,1040">®ciiti</w><w function="825,966,839,973">"</w><w function="844,968,881,1032">d</w><w function="881,985,899,1045">}</w> <w function="918,966,1022,1031">mit</w> <w function="1045,965,1308,1046">©inleitq</w><w function="1318,1020,1329,1033">.</w> <w function="1351,986,1388,1031">u</w> <w function="1427,966,1626,1047">turnen</w> <w function="1645,966,2098,1048">3lnmcrtungen</w> <w function="2147,1023,2161,1039">.</w><w function="2161,1021,2245,1058">^^</w>        <w function="2449,1021,2482,1058">^</w> <w function="169,1032,244,1110">t</w><w function="242,1047,292,1147">/</w><w function="311,1032,535,1109">lOV</w><w function="548,1084,572,1108">.</w>   <w function="649,1053,748,1131">fur</w> <w function="805,1053,1157,1137">Ungelefjrte</w><w function="1167,1108,1174,1117">.</w>    <w function="1290,1063,1305,1094">^</w><w function="1291,1098,1305,1111">-</w><w function="1305,1054,1430,1120">8on</w> <w function="1495,1032,1651,1136">^^rof</w>   <w function="1735,1054,1752,1115">1</w><w function="1770,1058,1792,1113">)</w>  <w function="1863,1053,2078,1137">^riebr</w><w function="2085,1109,2095,1118">.</w> <w function="2142,1032,2747,1157">oaCti^^Ctt</w><w function="2757,1111,2778,1131">.</w> </p>
      <p id="ID15">
        <w function="1077,1181,1091,1202">^</w>
        <w function="1084,1177,1093,1225">:</w>
        <w function="1093,1178,1109,1239">i</w>
        <w function="1109,1223,1117,1227">.</w>
        <w function="1110,1177,1226,1228">U44C</w>
        <w function="1225,1206,1231,1223">.</w> <w function="1273,1177,1375,1238">fem</w> <w function="1417,1182,1519,1227">cart</w> <w function="1580,1176,1664,1227">Wd</w> <w function="1726,1178,1743,1225">1</w> <w function="1774,1178,1843,1227">80</w> <w function="353,1278,539,1348">^^3tcu|</w><w function="549,1319,559,1330">.</w> <w function="601,1278,867,1347">3a^rbiid</w><w function="867,1296,879,1347">)</w><w function="888,1295,942,1335">cr</w> <w function="995,1284,1131,1333">1&lt;^98</w> <w function="1240,1327,1249,1340">,</w><w function="1257,1283,1357,1335">Xte</w> <w function="1415,1282,1730,1348">Ubeifet^nng</w> <w function="1793,1282,1850,1345">ift</w> <w function="1907,1288,2010,1334">пай</w><w function="2006,1299,2020,1347">)</w> <w function="2081,1289,2206,1349">itjiei</w> <w function="2267,1285,2581,1351">Сргпфафеп</w> <w function="2640,1284,2785,1338">Seite</w> <w function="170,1368,478,1437">meiftetEjaft</w><w function="486,1409,495,1419">.</w> <w function="574,1370,655,1423">Xa</w> <w function="698,1373,771,1420">bie</w> <w function="814,1372,841,1421">У</w><w function="845,1388,856,1420">(</w><w function="848,1371,975,1435">Ч1фа</w><w function="981,1373,995,1422">!</w><w function="995,1387,1008,1434">)</w><w function="1014,1387,1132,1423">пшп</w><w function="1140,1391,1151,1423">(</w><w function="1151,1387,1168,1435">^</w> <w function="1211,1378,1295,1423">beo</w> <w function="1339,1375,1353,1422">l</w><w function="1350,1387,1365,1438">}</w><w function="1371,1376,1446,1422">ebi</w> <w function="1507,1371,1814,1437">iнт^Jma^eo</w> <w function="1853,1374,1958,1422">bem</w> <w function="1999,1377,2242,1437">beut|фcn</w> <w function="2274,1374,2339,1426">£l</w><w function="2335,1390,2350,1439">)</w><w function="2356,1390,2373,1425">i</w> <w function="2420,1376,2510,1425">wcl</w><w function="2508,1390,2522,1437">)</w> <w function="2563,1377,2675,1437">üjui</w><w function="2679,1417,2691,1431">,</w> <w function="2733,1373,2782,1438">fo</w> <w function="173,1459,261,1525">l^at</w> <w function="302,1460,347,1511">Ш</w><w function="355,1501,363,1510">.</w> <w function="405,1462,494,1510">ben</w> <w function="539,1474,637,1511">au5</w> <w function="678,1471,860,1520">im|ern</w> <w function="909,1459,1023,1526">fiaff</w> <w function="1081,1460,1117,1513">2</w><w function="1117,1466,1131,1512">)</w><w function="1137,1464,1394,1524">^фtunqen</w> <w function="1445,1475,1545,1511">nno</w> <w function="1599,1460,1881,1527">qclaufiqcn</w> <w function="1943,1504,1956,1523">,</w><w function="1946,1461,2164,1521">^^ambu6</w> <w function="2205,1468,2520,1529">anqetuenbct</w> <w function="2567,1466,2670,1515">unb</w> <w function="2710,1466,2784,1513">bie</w> <w function="166,1547,344,1615">geilen</w> <w function="368,1563,432,1600">an</w> <w function="461,1548,722,1613">befonbeis</w> <w function="753,1567,763,1600">{</w><w function="763,1564,768,1598">:</w><w function="768,1549,828,1613">|cl</w><w function="828,1564,839,1611">)</w><w function="848,1550,1031,1602">obenen</w> <w function="1060,1551,1258,1602">Stellen</w> <w function="1291,1565,1342,1601">tn</w> <w function="1373,1565,1512,1600">cmon</w> <w function="1542,1550,1685,1600">\'Kam</w> <w function="1717,1551,2011,1611">апъПшдсп</w> <w function="2044,1550,2192,1612">laffcn</w> <w function="2267,1554,2283,1583">^</w><w function="2269,1551,2298,1605">}</w><w function="2292,1551,2316,1606">i</w><w function="2326,1592,2334,1601">.</w><w function="2343,1552,2391,1605">4</w> <w function="2424,1556,2437,1585">^</w><w function="2428,1589,2438,1602">-</w><w function="2438,1551,2784,1617">llUcbcrgabe</w> <w function="168,1638,252,1688">ber</w> <w function="284,1637,613,1704">Sdnlbeinnq</w> <w function="653,1641,734,1689">bcr</w> <w function="764,1637,1002,1705">iKajeftat</w> <w function="1029,1640,1051,1688">(</w><w function="1049,1640,1215,1692">Lottes</w><w function="1224,1683,1234,1695">,</w> <w function="1274,1638,1405,1701">t\'iob</w> <w function="1436,1642,1485,1687">21</w><w function="1488,1660,1500,1687">)</w><w function="1506,1682,1516,1698">,</w> <w function="1557,1642,1682,1689">шиЬ</w> <w function="1724,1653,1788,1689">an</w> <w function="1820,1638,2024,1690">ОЗешаи</w> <w function="2063,1642,2163,1690">nnb</w> <w function="2191,1640,2306,1692">iiBoI</w><w function="2309,1656,2320,1698">)</w><w function="2328,1641,2488,1706">lflamj</w> <w function="2527,1643,2613,1691">bce</w> <w function="2643,1641,2783,1693">\'Лио=</w> <w function="167,1725,339,1778">brwdö</w> <w function="391,1735,493,1780">tJOtt</w> <w function="547,1724,745,1780">feinem</w> <w function="789,1725,1156,1793">3Jiciftertt»ctf</w>  <w function="1212,1726,1490,1793">relißiöfcr</w> <w function="1531,1726,1800,1794">Di^tung</w> <w function="1850,1726,2117,1795">bcutfdjer</w> <w function="2156,1726,2348,1794">^»«9^</w> <w function="2397,1727,2729,1795">übertroffen</w><w function="2737,1732,2768,1781">/</w><w function="2770,1730,2783,1758">\'</w> </p>
      <p id="ID16">
        <w function="764,1934,918,2082">J^</w>
        <w function="902,1958,964,2080">/</w>
        <w function="948,1996,1016,2080">e</w> <w function="1112,1938,2188,2118">Berufsbegabung</w> </p>
      <p id="ID17">
        <w function="1429,2158,1441,2192">(</w>
        <w function="1450,2135,1537,2196">1er</w> </p>
      <p id="ID18">
        <w function="451,2238,1718,2361">Alttestamentliehen</w> <w function="1799,2238,2465,2383">Propheten</w><w function="2483,2344,2501,2365">.</w> </p>
      <p id="ID19">
        <w function="883,2410,919,2474">л</w> <w function="921,2410,1023,2473">On</w> <w function="1075,2410,1180,2473">Vvi</w><w function="1187,2439,1198,2472">)</w><w function="1204,2411,1224,2472">\</w><w function="1231,2464,1242,2474">,</w> <w function="1294,2411,1357,2474">D</w><w function="1366,2461,1378,2473">.</w> <w function="1425,2416,1514,2479">Fr</w><w function="1520,2461,1534,2478">.</w> <w function="1584,2413,2051,2478">Giesebrecht</w><w function="2054,2460,2071,2478">.</w> </p>
      <p id="ID1a">
        <w function="1059,2534,1191,2583">1897</w>     <w function="1298,2537,1313,2565">^</w><w function="1305,2536,1331,2598">;</w><w function="1331,2536,1343,2586">?</w><w function="1351,2538,1453,2585">rciö</w> <w function="1502,2532,1536,2584">4</w> <w function="1575,2534,1659,2585">^JJiC</w><w function="1663,2571,1674,2582">.</w> <w function="1714,2533,1783,2583">40</w> <w function="1825,2533,1855,2598">^^</w><w function="1855,2575,1869,2583">.</w><w function="1857,2531,1893,2596">^f</w><w function="1903,2573,1911,2583">.</w> <w function="349,2623,458,2677">Ъгх</w> <w function="510,2620,836,2684">otanbpunft</w> <w function="882,2624,969,2672">bcö</w> <w function="1021,2621,1143,2685">SSerf</w>   <w function="1202,2622,1308,2688">la^t</w> <w function="1356,2623,1443,2686">|1ф</w> <w function="1494,2623,1577,2673">baf</w><w function="1572,2637,1587,2685">)</w><w function="1592,2637,1646,2673">in</w> <w function="1696,2619,2131,2687">pfammenfaffen</w><w function="2138,2664,2149,2678">,</w>  <w function="2204,2620,2299,2686">bajj</w> <w function="2345,2622,2446,2687">i^m</w> <w function="2495,2626,2570,2672">bic</w> <w function="2611,2620,2784,2687">Cffen=</w> <w function="170,2696,370,2762">barung</w> <w function="411,2697,431,2747">(</w><w function="431,2695,598,2747">Lottes</w> <w function="637,2708,703,2744">axi</w> <w function="744,2696,817,2744">bic</w> <w function="859,2694,1143,2758">^^ropl^etcn</w> <w function="1183,2694,1311,2746">feine</w> <w function="1352,2676,1634,2747">àiebensart</w><w function="1639,2737,1649,2751">,</w>   <w function="1710,2694,1917,2755">fonbern</w> <w function="1959,2693,2065,2744">eine</w> <w function="2104,2692,2270,2759">flфcrc</w> <w function="2312,2694,2538,2745">^icalitat</w> <w function="2577,2692,2635,2758">ift</w><w function="2641,2736,2650,2747">,</w> <w function="2691,2692,2787,2756">Ъй%</w> <w function="164,2767,285,2820">aber</w> <w function="323,2768,398,2817">bte</w> <w function="438,2766,666,2820">9tealitàt</w> <w function="717,2767,800,2816">ber</w> <w function="852,2763,1200,2833">Cffenbarung</w>  <w function="1257,2767,1297,2817">if</w><w function="1297,2782,1308,2828">)</w><w function="1314,2782,1362,2817">m</w>  <w function="1419,2770,1544,2831">тф1</w> <w function="1592,2781,1658,2818">an</w> <w function="1708,2773,1782,2816">btc</w> <w function="1833,2764,1920,2829">аи|</w><w function="1920,2780,1935,2822">}</w><w function="1941,2766,2079,2831">сгиф</w> <w function="2110,2764,2444,2830">mtrafeUjafte</w>  <w function="2497,2765,2596,2816">Slrt</w> <w function="2646,2768,2684,2816">il</w><w function="2680,2780,2696,2831">)</w><w function="2701,2768,2784,2816">reâ</w> <w function="165,2838,410,2906">ЖоЩидб</w> <w function="450,2840,704,2906">gebunben</w> <w function="745,2835,969,2903">ег|фе1п1</w><w function="975,2874,984,2884">.</w> </p>
      <p id="ID1b">
        <w function="352,3030,491,3083">1895</w> <w function="533,3030,592,3097">ift</w> <w function="633,3029,687,3081">in</w> <w function="725,3029,976,3094">fünfter</w> <w function="1017,3030,1173,3095">polltg</w> <w function="1213,3044,1309,3081">neu</w> <w function="1349,3030,1670,3081">bearbeiteter</w> <w function="1709,3027,1986,3091">Sluflagc</w> <w function="2027,3028,2294,3095">erfфlenen</w><w function="2297,3072,2310,3088">,</w> <w function="2360,3029,2573,3095">ïnapper</w> <w function="2618,3028,2787,3098">gefaxt</w> <w function="167,3139,271,3191">unb</w> <w function="307,3136,575,3204">шefenthф</w> <w function="615,3136,893,3204">njo^lfeiler</w> <w function="941,3137,1023,3189">а\ь</w> <w function="1075,3138,1149,3187">bic</w> <w function="1188,3136,1294,3199">frul</w><w function="1294,3151,1305,3197">)</w><w function="1311,3151,1423,3188">eren</w> <w function="1460,3135,1714,3203">âluflagen</w><w function="1730,3176,1739,3186">.</w> </p>
      <p id="ID1c">
        <w function="170,3432,224,3504">2</w>
        <w function="224,3444,238,3498">)</w>
        <w function="244,3430,302,3504">ie</w> <w function="346,3430,1114,3518">Dffenbttruttgêrdigion</w> <w function="1176,3428,1292,3508">auf</w> <w function="1354,3428,1530,3514">i^rcr</w> <w function="1586,3426,2080,3512">öor^riftli^en</w> <w function="2130,3426,2766,3516">enttuiiflungêftufc</w><w function="2770,3486,2786,3502">.</w> </p>
      <p id="ID1d">
        <w function="890,3554,976,3608">Ш\</w>
        <w function="980,3596,988,3604">.</w> <w function="1032,3554,1098,3606">10</w><w function="1102,3598,1114,3614">,</w><w function="1118,3552,1188,3604">40</w><w function="1196,3592,1206,3604">.</w>   <w function="1282,3554,1364,3618">3n</w> <w function="1402,3552,1480,3618">§a</w><w function="1484,3554,1500,3604">(</w><w function="1506,3552,1798,3606">blebcrbonb</w> <w function="1840,3554,1868,3604">3</w><w function="1868,3552,1882,3604">)</w><w function="1886,3552,1926,3604">»</w><w function="1930,3596,1940,3604">.</w> <w function="1984,3552,2050,3604">12</w><w function="2058,3590,2068,3604">.</w> </p>
      <p id="ID1e">
        <w function="174,3750,370,3874">Die</w> <w function="450,3750,1120,3874">Litteratur</w> <w function="1214,3748,1426,3868">des</w> <w function="1518,3748,1870,3868">Alten</w> <w function="1952,3744,2790,3874">Testamentes</w> </p>
      <p id="ID1f">
        <w function="812,3921,973,3983">nach</w> <w function="1020,3921,1131,3983">der</w> <w function="1178,3918,1482,4000">Zeitfolge</w> <w function="1528,3919,1696,3980">ihrer</w> <w function="1742,3917,2147,3999">Entstehung</w> </p>
      <p id="ID20">
        <w function="1437,4034,1523,4063">von</w> </p>
      <p id="ID21">
        <w function="1160,4104,1235,4174">G</w>
        <w function="1236,4153,1254,4173">.</w> <w function="1304,4102,1798,4174">Wildeboer</w><w function="1798,4151,1817,4183">,</w> </p>
      <p id="ID22">
        <w function="733,4197,865,4238">Theol</w>
        <w function="870,4226,879,4236">.</w> <w function="910,4196,1013,4237">Doet</w><w function="1016,4228,1027,4238">.</w> <w function="1056,4197,1142,4237">und</w> <w function="1170,4198,1246,4238">ord</w><w function="1251,4227,1262,4237">.</w> <w function="1292,4196,1508,4238">Professor</w> <w function="1534,4197,1607,4237">der</w> <w function="1633,4194,1862,4246">Theologie</w> <w function="1889,4205,1936,4233">m</w> <w function="1965,4193,2209,4244">Groningen</w><w function="2216,4223,2226,4232">.</w> </p>
      <p id="ID23">
        <w function="170,4283,357,4340">unter</w> <w function="382,4284,757,4353">Mitwirkung</w> <w function="797,4283,896,4338">des</w> <w function="921,4284,1061,4338">Verf</w><w function="1062,4327,1072,4337">.</w> <w function="1112,4302,1216,4338">aus</w> <w function="1258,4284,1383,4338">dem</w> <w function="1407,4282,1862,4337">Hollandifechen</w> <w function="1899,4281,2068,4334">ubers</w><w function="2078,4324,2088,4334">.</w> <w function="2126,4298,2228,4333">von</w> <w function="2257,4281,2355,4334">Pfr</w><w function="2362,4323,2373,4334">.</w> <w function="2411,4281,2493,4335">Dr</w><w function="2501,4324,2511,4336">.</w> <w function="2542,4284,2576,4337">F</w><w function="2575,4322,2590,4338">.</w> <w function="2619,4282,2775,4338">Rlsoh</w><w function="2778,4322,2792,4337">.</w> </p>
      <p id="ID24">
        <w function="694,4405,754,4461">gr</w>
        <w function="759,4433,771,4445">.</w> <w function="812,4390,848,4443">8</w><w function="852,4432,863,4442">.</w>   <w function="939,4391,1095,4459">^reiê</w> <w function="1133,4393,1167,4445">9</w> <w function="1208,4392,1309,4448">ШЬ</w>   <w function="1387,4392,1468,4458">3n</w> <w function="1505,4390,1903,4458">§alWeberbanb</w> <w function="1944,4388,2029,4444">Ш«</w><w function="2034,4431,2045,4442">.</w> <w function="2089,4388,2158,4441">10</w><w function="2164,4429,2174,4441">.</w><w function="2181,4389,2249,4441">60</w><w function="2256,4431,2269,4443">.</w> </p>
      <p id="ID25">
        <w function="353,4498,486,4567">^tof</w>
        <w function="492,4541,503,4551">.</w> <w function="545,4498,590,4554">%</w><w function="594,4541,604,4551">.</w> <w function="644,4497,985,4567">©iegfrieb</w><w function="1001,4512,1011,4553">:</w> <w function="1077,4542,1108,4560">„</w><w function="1111,4500,1226,4555">Sir</w> <w function="1283,4498,1560,4567">empfeitlen</w> <w function="1615,4499,1712,4551">baè</w> <w function="1769,4496,1913,4552">Serf</w> <w function="1965,4495,2125,4549">einem</w> <w function="2171,4496,2333,4563">ЗеЬеп</w><w function="2336,4539,2349,4557">,</w> <w function="2408,4497,2491,4548">ber</w> <w function="2547,4493,2635,4564">fic^</w> <w function="2687,4494,2793,4547">eine</w> <w function="173,4568,427,4626">lebenWae</w> <w function="483,4570,817,4639">SCnfc|auuna</w> <w function="871,4585,888,4625">t</w><w function="888,4585,905,4625">)</w><w function="909,4585,976,4625">on</w> <w function="1034,4573,1119,4626">ber</w> <w function="1174,4570,1513,4640">©ntwidflung</w> <w function="1575,4571,1658,4625">ber</w> <w function="1713,4567,2018,4637">^еЬгш|феп</w> <w function="2074,4567,2331,4622">Literatur</w> <w function="2385,4581,2446,4634">%\x</w> <w function="2499,4564,2795,4635">oerfфaffen</w> <w function="171,4640,403,4710">Юип|ф1</w><w function="408,4649,438,4669">"</w>                                                                    <w function="1852,4639,2036,4708">К%%г^\</w><w function="2040,4681,2050,4692">.</w> <w function="2089,4638,2171,4694">Ш</w><w function="2175,4679,2186,4691">.</w><w function="2191,4639,2259,4707">=3</w><w function="2260,4640,2282,4693">*</w><w function="2284,4654,2313,4708">9</w><w function="2321,4681,2331,4692">.</w> <w function="2372,4636,2513,4691">1895</w> <w function="2552,4638,2628,4693">Ш</w><w function="2633,4678,2644,4691">.</w> <w function="2687,4637,2754,4691">12</w><w function="2758,4679,2770,4690">.</w><w function="2776,4643,2792,4703">)</w> </p>
      <pb/>
      <milestone n="2" type="page"/></body></text></TEI.2>
',
            'SSerlog öon  Danbent)oecî &amp; ÎJupredjt  itt ©ottingen. 
      
        3m October ег|фешеп fcrnei 
      
        J
        , Meînhold^ Prof. Lie,  Die Jesajaerzählungen (lesaja 36—39). 
      
        Eine liistorisch-kritiM\'lu\' riitersuchimu^.                                 3 Mk. 
      
        M
        . Fried lander,  Der vorchristliche jüdische Gnosticismus. 
      
        Pi44s etwa  Mk. 2.S0. 
      
        1л 1 /Nt&gt;    ®ciiti"d} mit ©inleitq. u turnen 3lnmcrtungen .^^        ^ t/lOV.   fur Ungelefjrte.    ^-8on ^^rof   1)  ^riebr. oaCti^^Ctt. 
      
        ^
        :
        i
        .
        U44C
        . fem cart Wd 1 80 ^^3tcu|. 3a^rbiid)cr 1&lt;^98 ,Xte Ubeifet^nng ift пай) itjiei Сргпфафеп Seite meiftetEjaft. Xa bie У(Ч1фа!)пшп(^ beo l}ebi iнт^Jma^eo bem beut|фcn £l)i wcl) üjui, fo l^at Ш. ben au5 im|ern fiaff 2)^фtunqen nno qclaufiqcn ,^^ambu6 anqetuenbct unb bie geilen an befonbeis {:|cl)obenen Stellen tn cmon \'Kam апъПшдсп laffcn ^}i.4 ^-llUcbcrgabe ber Sdnlbeinnq bcr iKajeftat (Lottes, t\'iob 21), шиЬ an ОЗешаи nnb iiBoI)lflamj bce \'Лио= brwdö tJOtt feinem 3Jiciftertt»ctf  relißiöfcr Di^tung bcutfdjer ^»«9^ übertroffen/\' 
      
        J^
        /
        e Berufsbegabung 
      
        (
        1er 
      
        Alttestamentliehen Propheten. 
      
        л On Vvi)\, D. Fr. Giesebrecht. 
      
        1897     ^;?rciö 4 ^JJiC. 40 ^^.^f. Ъгх otanbpunft bcö SSerf   la^t |1ф baf)in pfammenfaffen,  bajj i^m bic Cffen= barung (Lottes axi bic ^^ropl^etcn feine àiebensart,   fonbern eine flфcrc ^icalitat ift, Ъй% aber bte 9tealitàt ber Cffenbarung  if)m  тф1 an btc аи|}сгиф mtrafeUjafte  Slrt il)reâ ЖоЩидб gebunben ег|фе1п1. 
      
        1895 ift in fünfter polltg neu bearbeiteter Sluflagc erfфlenen, ïnapper gefaxt unb шefenthф njo^lfeiler а\ь bic frul)eren âluflagen. 
      
        2
        )
        ie Dffenbttruttgêrdigion auf i^rcr öor^riftli^en enttuiiflungêftufc. 
      
        Ш\
        . 10,40.   3n §a(blebcrbonb 3)». 12. 
      
        Die Litteratur des Alten Testamentes 
      
        nach der Zeitfolge ihrer Entstehung 
      
        von 
      
        G
        . Wildeboer, 
      
        Theol
        . Doet. und ord. Professor der Theologie m Groningen. 
      
        unter Mitwirkung des Verf. aus dem Hollandifechen ubers. von Pfr. Dr. F. Rlsoh. 
      
        gr
        . 8.   ^reiê 9 ШЬ   3n §alWeberbanb Ш«. 10.60. 
      
        ^tof
        . %. ©iegfrieb: „Sir empfeitlen baè Serf einem ЗеЬеп, ber fic^ eine lebenWae SCnfc|auuna t)on ber ©ntwidflung ber ^еЬгш|феп Literatur %\x oerfфaffen Юип|ф1"                                                                    К%%г^\. Ш.=3*9. 1895 Ш. 12.)'
        ]
        ];
    }

    /**
     * @var TeiProcessor
     */
    protected $fixture;

    public function setUp()
    {
        parent::setUp();
        $this->fixture = new TeiProcessor();
    }

    /**
     * @dataProvider teiDataProvider
     */
    public function testStrippingOfTags($teiContent, $expected)
    {
        $result = $this->fixture->process($teiContent);

       $this->assertSame($expected, $result);
    }
}
