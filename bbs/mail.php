<?php
require_once './_common.php';
require_once G5_LIB_PATH . '/mailer.lib.php';

if ($_POST['tel1'] !='') {
    $name         = $_POST['name'];
    $tel1         = $_POST['tel1'];
    $area         = $_POST['area'];
    $budget       = $_POST['budget'];
    $form_index       = $_POST['form_index'];

    $title = '상호명 창업문의 - ' . $name . '님';

    $content = '
    <div style="font-family:\'Segoe UI\',Arial,sans-serif; max-width:650px; margin:0 auto; background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 4px 15px rgba(50,53,205,0.15);">

      <div style="background:#3235cd; color:#fff; padding:24px; text-align:center;">
        <h2 style="margin:0; font-size:22px; letter-spacing:1px;">상호명</h2>
        <p style="margin:6px 0 0; font-size:14px; opacity:0.9;">창업 문의 접수 알림</p>
      </div>

      <div style="padding:28px;">
        <div style="margin-bottom:12px; padding:14px 18px; background:#eaebff; border-radius:8px;">
          <span style="display:block; font-size:12px; font-weight:bold; color:#3235cd; margin-bottom:6px;">성함</span>
          <span style="font-size:15px; color:#222;">'.$name.'</span>
        </div>

        <div style="margin-bottom:12px; padding:14px 18px; background:#f9f9ff; border-radius:8px;">
          <span style="display:block; font-size:12px; font-weight:bold; color:#3235cd; margin-bottom:6px;">연락처</span>
          <span style="font-size:15px; color:#222;">'.$tel1.'</span>
        </div>

        <div style="margin-bottom:12px; padding:14px 18px; background:#eaebff; border-radius:8px;">
          <span style="display:block; font-size:12px; font-weight:bold; color:#3235cd; margin-bottom:6px;">창업희망지역</span>
          <span style="font-size:15px; color:#222;">'.$area.'</span>
        </div>

        <div style="margin-bottom:12px; padding:14px 18px; background:#f9f9ff; border-radius:8px;">
          <span style="display:block; font-size:12px; font-weight:bold; color:#3235cd; margin-bottom:6px;">이메일</span>
          <span style="font-size:15px; color:#222;">'.$budget.'</span>
        </div>

        <div style="margin-bottom:12px; padding:14px 18px; background:#eaebff; border-radius:8px;">
          <span style="display:block; font-size:12px; font-weight:bold; color:#3235cd; margin-bottom:6px;">내용</span>
          <span style="font-size:15px; color:#222;">'.$form_index.'</span>
        </div>

        <p style="margin-top:24px; font-size:12px; color:#777; text-align:center; border-top:1px dashed #eaebff; padding-top:12px;">
          ※ 본 메일은 자동 발송된 안내 메일입니다. 
        </p>
      </div>

    </div>';

    // 메일 발송
    mailer($name, 'vworks02@naver.com', 'rbska98@naver.com', $title , $content , 1);

    // DB 저장 (추가된 필드들을 여유분 필드인 wr_x에 매칭했습니다)
    $sql = " insert into g5_email_data
                set name     = '$name',
                    phone    = '$tel1',
                    location = '$area',
                    budget   = '$budget',
                    content  = '$form_index'";
            
    sql_query($sql);
}