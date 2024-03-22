<?php
require('./backend/core.php');

$best_win_apps = new BestWinApps;

$result = $best_win_apps->get_apps($_GET['q']);
$keywords = str_replace('+', ' ', $_GET['q']);

$display_mode = $_COOKIE['mode'] ?? 'light';
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Best|Win|Apps</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <link rel="stylesheet" href="./assets/css/index.css">

  <style>
    .dark-mode ::placeholder {
      color: #30adbf !important;
      opacity: 1;
      /* Firefox */
    }

    .light-mode ::placeholder {
      color: #9ccbdb !important;
      opacity: 1;
      /* Firefox */
    }

    .dark-mode input:focus {
      background-color: #3c546e !important;
    }

    @font-face {
      font-family: rainy;
      src: url('./assets/fonts/rainyhearts.ttf');
    }

    * {
      font-family: rainy;
    }
  </style>
</head>

<body>
  <div class="<?= $display_mode; ?>-mode">
    <div class="container-fluid content">
      <div class="row pb-5">
        <div class="col-md-2 col-lg-3 col-xl-4"></div>
        <div class="col-md-8 col-lg-6 col-xl-4">
          <img class="my-5" src="./assets/images/cat_<?= $display_mode; ?>.gif" alt="dark" width="100%" height="auto">
          <input type="text" class="form-control search-box" placeholder="Type to search..." autocomplete="off" value="<?= $keywords; ?>">
          <p class="tip">Tip: You can also search for apps with specific license. For example, 'Open source video player'</p>
        </div>
        <div class="col-md-2 col-lg-3 col-xl-4"></div>
      </div>

      <div class="row">
        <div class="col col-lg-1 col-xl-2"></div>
        <div class="col-md-12 col-lg-10 col-xl-8">
          <div class="container-fluid">
            <div class="row" id="item-box">
              <?php
              if ($result["result"]->num_rows > 0) {
                // output data of each row
                while ($row = $result["result"]->fetch_assoc()) {
              ?>
                <div class="col-md-4 my-2">
                  <div class="box-container p-1 d-flex">
                    <div class="left-border"></div>
                    <a href="<?= $row['url'];?>" target="_blank" class="item" onclick="save('<?= $row['app_id'];?>')">
                      <div class="box position-relative w-100" style="height: 90px;">
                        <div class="top-border"></div>
                        <div class="d-flex">  
                          <div>
                            <img src="./assets/app_icons/<?= $row['icon_name'];?>.png" alt="png" style="width: 50px;" class="p-1">
                          </div>
                          <div class="w-100 px-1">
                            <h6 class="fw-bold my-1 item-title"><?= $row['name'];?></h6>
                            <p class="item-desc"><?= $row['description'];?></p>
                          </div>
                        </div>
                        <div class="bottom-border"></div>
                      </div>
                    </a>
                    <div class="right-border"></div>
                  </div>
                </div>
              <?php
                }
              }
              ?>
            </div>
            
            <div class="p-1">
              <button type="button" id="view-more" class="btn btn-sm btn-info form-control text-center"  <?= $result["more"] ? "" : 'style="display: none;"'; ?>>
                View More
              </button>
            </div>

            <div class="row pt-5">
              <div class="col-md-6">
                <b class="text-area-title">Short subtitle that explain paragraph below</b>
                <p class="text-area-desc">At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum
                  deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non
                  provident, similique sunt in culpa qui officia deserunt mollitia animi.</p>
              </div>
              <div class="col-md-6">
                <b class="text-area-title">Second subtitle here</b>
                <p class="text-area-desc">Omnis voluptas assumenda est, omnis dolor repellendus. Temporibus autem quibesdam et aut officiis
                  debitis aut rerum necessitatibus saepe eveniet ut et voluptates repudiandae sint et molestiae non
                  recusandae. Itaque earum rerum hic tenetur a sapiente delectus.</p>
              </div>
            </div>
          </div>
        </div>
        <div class="col col-lg-1 col-xl-2"></div>
      </div>
    </div>

    <div class="option footer">
      <div class="container-fluid border-top-2">
        <div class="row">
          <div class="col col-lg-1 col-xl-2"></div>
          <div class="col-md-12 col-lg-10 col-xl-8">
            <div class="container-fluid p-1">
              <div class="row">
                <div class="col-md-6">
                  <div>
                    <b class="footer-heading">Copyright 2024</b>
                    <p class="m-0 footer-content"><small>Website made with * by Jouni Flemming</small></p>
                  </div>
                </div>
                <div class="col-md-6">
                  <b class="footer-heading">Contact</b>
                  <div>
                    <div style="float: left;">
                      <p class="m-0 footer-content"><small>bestwinapps@info.com</small></p>
                    </div>
                    <div style="float: right;" class="tumbler-wrapper p-0">
                      <div class="tumbler ms-1"></div>
                      <div class="d-flex justify-content-center align-items-center w-100 h-100" onclick="setVisibleMode('light')">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-sun text-white" viewBox="0 0 16 16">
                          <path d="M8 11a3 3 0 1 1 0-6 3 3 0 0 1 0 6m0 1a4 4 0 1 0 0-8 4 4 0 0 0 0 8M8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0m0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13m8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5M3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8m10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0m-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0m9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707M4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708" />
                        </svg>
                      </div>
                      <div class="d-flex justify-content-center align-items-center w-100 h-100" onclick="setVisibleMode('dark')">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-moon text-white" viewBox="0 0 16 16">
                          <path d="M6 .278a.77.77 0 0 1 .08.858 7.2 7.2 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277q.792-.001 1.533-.16a.79.79 0 0 1 .81.316.73.73 0 0 1-.031.893A8.35 8.35 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.75.75 0 0 1 6 .278M4.858 1.311A7.27 7.27 0 0 0 1.025 7.71c0 4.02 3.279 7.276 7.319 7.276a7.32 7.32 0 0 0 5.205-2.162q-.506.063-1.029.063c-4.61 0-8.343-3.714-8.343-8.29 0-1.167.242-2.278.681-3.286" />
                        </svg>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col col-lg-1 col-xl-2"></div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  <script src="./assets/js/index.js"></script>
</body>

</html>