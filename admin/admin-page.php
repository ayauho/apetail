<?php
 if (!current_user_can('manage_options')) die;
 if($_GET['tab']=='settings') $tab='settings';
 elseif($_GET['tab']=='info') $tab='info';
 else $tab=null;
?>
<div class="wrap">
  <h1 class="title"><? esc_html_e('ApeTail admin page','apetail')?></h1>
  <?php settings_errors(); ?>

  <nav class="nav-tab-wrapper">
    <a href="?page=apetail_settings&tab=settings" class="nav-tab <?php if($tab==null||$tab=='settings'):?>nav-tab-active<?php endif; ?>">Settings</a>
    <a href="?page=apetail_settings&tab=info" class="nav-tab <?php if($tab=='info'):?>nav-tab-active<?php endif; ?>">Info</a>
  </nav>

    <div class="tab-content">
    <?php switch($tab) :
      case 'info':
?>
    <p>With all questions, please contact the developer with the command <b>#private ayaho</b>. If you need translation for your language - become premium and will receive it in 5 days. If you have suggestions and development ideas, please share and receive a chance be rewarded with equity tokens of ApeTail. If you have a bug report, please let me know as soon as possible.</p>
    <a class='big' target='_blank' href='https://fundaria.com/ApeTail'>ApeTail web page</a>
    <a class='big' target='_blank' href='https://docs.google.com/document/d/1X0w_3nqLPkHE2ENyGNCXkrnC8wNwRU0LWqGQBcsIK-Y/edit#heading=h.n93i0lzh8fwp'>User Guide</a>
    <a class='big' target='_blank' href='https://docs.google.com/document/d/1XKKh2k7PhKDLqcg1mJiE6abEeedY73sGCNsveCbsqU0/edit?usp=sharing'>Premium Service Cost & Advantages</a>    
    <a class='big' target='_blank' href='https://fundaria.com/ApeTail/qa'>Questions & Answers</a>
    <a class='big' target='_blank' href='https://fundaria.com/ApeTail/advantages'>Advantages to other similar communication systems</a>
    <a class='big' target='_blank' href='https://docs.google.com/document/d/1uOTJwx9MtJfd9z4W5czr-whqIo9zuovwJTyeuwj6Bg8/edit?usp=sharing'>Investment opportunity</a>
    <img style='max-width:100%;height:auto;' src='https://fundaria.com/images/ApeTail/roadmap.jpg' />
<?php

        break;
      default:
?>
      <form method="post" action="options.php">
          <?php
              settings_fields('apetail_settings');
              do_settings_sections('apetail_settings');
              submit_button();
          ?>    
      </form>
<?php
        break;
    endswitch; ?>
    </div>
</div>