<?php
$sub_menu = "200800";
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, 'r');

$fr_date = isset($_REQUEST['fr_date']) ? preg_replace('/[^0-9 :\-]/i', '', $_REQUEST['fr_date']) : G5_TIME_YMD;
$to_date = isset($_REQUEST['to_date']) ? preg_replace('/[^0-9 :\-]/i', '', $_REQUEST['to_date']) : G5_TIME_YMD;

$g5['title'] = '월별 접속자집계';
include_once('./visit.sub.php');

// [데이터 가공 로직]
$max = 0;
$sum_count = 0;
$arr = array();

$sql = " select SUBSTRING(vs_date,1,7) as vs_month, SUM(vs_count) as cnt
            from {$g5['visit_sum_table']}
            where vs_date between '{$fr_date}' and '{$to_date}'
            group by vs_month
            order by vs_month desc ";
$result = sql_query($sql);
for ($i=0; $row=sql_fetch_array($result); $i++) {
    $arr[$row['vs_month']] = $row['cnt'];
    if ($row['cnt'] > $max) $max = $row['cnt'];
    $sum_count += $row['cnt'];
}
?>

<style>
    /* 전체 래퍼 */
    .vst-layout-container {
        font-family: "Pretendard Variable", sans-serif !important;
        background-color: #fff;
        border-radius: 20px;
        box-shadow: 0 5px 25px rgba(0,0,0,0.04);
        padding: 30px;
        margin-top: 20px;
        color: #333;
    }

    /* 1. 상단 그래프 영역 (가로 스크롤) */
    .vst-chart-wrap {
        display: flex;
        align-items: flex-end;
        height: 240px;
        margin-bottom: 40px;
        padding-bottom: 20px;
        border-bottom: 2px solid #f1f3f5;
        overflow-x: auto;
        gap: 20px; /* 월별은 간격을 좀 더 넓게 */
        padding-left: 10px;
        padding-right: 10px;
    }
    .vst-chart-wrap::-webkit-scrollbar { height: 8px; }
    .vst-chart-wrap::-webkit-scrollbar-thumb { background: #e9ecef; border-radius: 4px; }

    .vst-chart-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        min-width: 50px;
        position: relative;
        cursor: pointer;
        flex-shrink: 0;
    }

    .vst-bar-value {
        font-size: 11px;
        font-weight: 700;
        color: #3235cd;
        margin-bottom: 6px;
        opacity: 0;
        animation: vstFadeUp 0.6s forwards;
    }
    .vst-bar-stick {
        width: 24px;
        background: #e9ecef;
        border-radius: 6px 6px 0 0;
        height: 0; 
        transition: height 1s cubic-bezier(0.25, 0.8, 0.25, 1);
        min-height: 4px;
    }
    .vst-bar-stick.highlight {
        background: #3235cd; 
        box-shadow: 0 4px 12px rgba(50, 53, 205, 0.3);
    }
    .vst-chart-item:hover .vst-bar-stick {
        opacity: 0.8;
        transform: scaleY(1.05);
        background: #3235cd;
    }
    .vst-bar-label { 
        margin-top: 10px; 
        font-size: 12px; 
        font-weight: 600; 
        color: #888; 
    }

    /* 2. 리스트 헤더 */
    .vst-list-header {
        display: flex;
        background: #f8f9fa;
        padding: 15px;
        border-radius: 12px;
        font-weight: 700;
        color: #888;
        font-size: 13px;
        margin-bottom: 10px;
        border: 1px solid #eee;
    }

    /* 3. 리스트 로우 */
    .vst-list-row {
        display: flex;
        align-items: center;
        padding: 16px 15px;
        border-bottom: 1px solid #f1f1f1;
        font-size: 14px;
        color: #333;
        transition: 0.2s;
    }
    .vst-list-row:hover { background: #fcfcfc; }
    .vst-list-row:last-child { border-bottom: none; }

    /* 컬럼 스타일 */
    .col-month { width: 140px; font-weight: 700; color: #555; }
    .col-month a { text-decoration: none; color: #333; transition: 0.2s; }
    .col-month a:hover { color: #3235cd; text-decoration: underline; }

    .col-graph { flex: 1; padding: 0 20px; }
    .col-count { width: 120px; text-align: right; font-weight: 700; color: #333; }
    .col-rate { width: 80px; text-align: right; color: #888; font-size: 13px; }

    /* 월 뱃지 */
    .month-badge {
        display: inline-block;
        padding: 6px 12px;
        background: #f8f9fa;
        border-radius: 8px;
        color: #555;
        font-size: 14px;
        border: 1px solid #eee;
    }
    .vst-list-row:hover .month-badge {
        border-color: #3235cd;
        color: #3235cd;
        background: #fff;
    }

    /* 가로 바 그래프 */
    .vst-progress-bg {
        width: 100%;
        height: 6px;
        background: #f1f3f5;
        border-radius: 3px;
        overflow: hidden;
    }
    .vst-progress-fill {
        height: 100%;
        background: #3235cd;
        border-radius: 3px;
        transition: width 1s ease;
    }

    /* 합계 행 */
    .vst-list-footer {
        display: flex;
        align-items: center;
        background: #f8f9fa;
        padding: 15px;
        border-radius: 12px;
        margin-top: 10px;
        font-weight: 800;
        color: #3235cd;
        font-size: 15px;
        border: 1px solid #eee;
    }

    @keyframes vstFadeUp { to { opacity: 1; transform: translateY(0); } }

    /* 반응형 */
    @media (max-width: 768px) {
        .vst-layout-container { padding: 20px 15px; }
        .vst-chart-wrap { height: 200px; gap: 10px; }
        .vst-list-header { display: none; }
        
        .col-graph { display: none; } /* 모바일에서 그래프 바 숨김 */
        .col-month { width: auto; flex: 1; }
        .col-count { width: auto; flex: 0 0 100px; }
        .col-rate { width: auto; flex: 0 0 60px; }
        
        .vst-list-row { gap: 10px; }
        .month-badge { font-size: 13px; padding: 4px 8px; }
    }
</style>

<div class="vst-layout-container">
    
    <div class="vst-chart-wrap">
        <?php
        // 차트는 과거 -> 미래(왼쪽 -> 오른쪽) 순서가 자연스러우므로 배열을 뒤집습니다.
        $chart_arr = array_reverse($arr, true); 
        
        foreach ($chart_arr as $month => $count) {
            // 높이 계산
            $height_rate = ($max > 0) ? round(($count / $max) * 100) : 0;
            if($count > 0 && $height_rate < 5) $height_rate = 5;
            
            $highlight = ($count > 0 && $count == $max) ? 'highlight' : '';
            
            // 링크 URL (일별 상세로 이동)
            $link_url = "./visit_date.php?fr_date={$month}-01&to_date={$month}-31";
        ?>
        <div class="vst-chart-item" onclick="location.href='<?php echo $link_url ?>'">
            <div class="vst-bar-value" style="animation-delay: 0.1s">
                <?php echo ($count > 0) ? number_format($count) : ''; ?>
            </div>
            <div class="vst-bar-stick <?php echo $highlight ?>" style="height: <?php echo $height_rate ?>%;"></div>
            <div class="vst-bar-label"><?php echo $month ?></div>
        </div>
        <?php } ?>
    </div>

    <div class="vst-list-wrap">
        <div class="vst-list-header">
            <div class="col-month">년-월</div>
            <div class="col-graph">비율 그래프</div>
            <div class="col-count">접속자 수</div>
            <div class="col-rate">비율(%)</div>
        </div>

        <?php
        $has_data = false;
        // $arr는 쿼리 결과 그대로 (최신순 DESC)
        foreach ($arr as $key => $value) {
            $has_data = true;
            $count = $value;
            $rate = ($sum_count > 0) ? ($count / $sum_count * 100) : 0;
            $s_rate = number_format($rate, 1);
            
            $link_url = "./visit_date.php?fr_date={$key}-01&to_date={$key}-31";
        ?>
        <div class="vst-list-row">
            <div class="col-month">
                <a href="<?php echo $link_url ?>">
                    <span class="month-badge"><?php echo $key ?></span>
                </a>
            </div>
            
            <div class="col-graph">
                <div class="vst-progress-bg">
                    <div class="vst-progress-fill" style="width: <?php echo $s_rate ?>%;"></div>
                </div>
            </div>

            <div class="col-count">
                <?php echo number_format($count) ?>명
            </div>
            
            <div class="col-rate">
                <?php echo $s_rate ?>%
            </div>
        </div>
        <?php } ?>

        <?php if(!$has_data) { ?>
        <div style="text-align:center; padding:80px 0; color:#999;">
            <span style="display:block; font-size:40px; margin-bottom:10px;">🗓️</span>
            데이터가 없습니다.
        </div>
        <?php } ?>

        <div class="vst-list-footer">
            <div class="col-month">전체 합계</div>
            <div class="col-graph"></div>
            <div class="col-count"><?php echo number_format($sum_count) ?>명</div>
            <div class="col-rate">100%</div>
        </div>
    </div>

</div>

<?php
include_once('./admin.tail.php');
?>