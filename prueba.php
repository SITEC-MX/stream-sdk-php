<?php
require_once __DIR__ . "/vendor/autoload.php";

use \Mpsoft\Stream\Stream;

$token = "aplicacion-token";

$stream = new Stream($token);

$video_id = 1;

$video_uuid = NULL;
$video = $stream->GET_Videos(array("id"=>$video_id));
if($video["estado"] == Stream::OK)
{
    $video_uuid = $video["resultado"]["valores"]["uuid"];
}

$token = NULL;
$videotoken = $stream->POST_Videotokenes(NULL, NULL, array("video_id"=>$video_id, "vigencia_tiempo"=>3600));
if($videotoken["estado"] == Stream::OK)
{
    $token = urlencode( $videotoken["resultado"]["token"] );
}
?>
<html>
<head>
    <script src="https://cdn.svc-sitec.net/stream/player.js"></script>

    <style>
        #video
        {
            width: 50%;
            margin: auto;
            display: block;
        }
    </style>
</head>

<body>
    <div class="sitec-stream-player" id="video" data-uuid="<?php echo $video_uuid; ?>" data-token="<?php echo $token; ?>"></div>
</body>


</html>