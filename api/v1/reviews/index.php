<?php
declare(strict_types=1);

require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
header('Content-Type: application/json');

use Modules\DB;

try {
    $DB = new DB();
} catch (Exception $e) {
    exit(json_encode(['success' => false, 'message' => $e->getMessage()]));
}

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        if (!empty($id = $_GET['id'])) {
            if (is_numeric($id)) {
                $additionalFields = $_GET['fields'] ? explode(',', htmlspecialchars($_GET['fields'])) : null;
                try {
                    $review_info = $DB->getReviewById((int)$id, $additionalFields);
                    $review_info['success'] = true;
                    exit(json_encode($review_info));
                } catch (Exception $e) {
                    header('HTTP/1.1 404 Not Found');
                    exit(json_encode(['success' => false, 'message' => $e->getMessage()]));
                }
            } else {
                header('HTTP/1.1 400 Bad Request');
                exit(json_encode(['id' => $id, 'success' => 'false', 'message' => 'Id should be integer']));
            }
        } else {
            $rateSort = null;
            $dateSort = null;
            if (!empty($_GET['sort'])) {
                foreach (explode(',', $_GET['sort']) as $value) {
                    $sortArr = explode(':', $value);
                    if ($sortArr[0] == 'rate')
                        $rateSort = htmlspecialchars($sortArr[1]);
                    elseif ($sortArr[0] == 'date')
                        $dateSort = htmlspecialchars($sortArr[1]);
                }
            }

            try {
                $response = $DB->getReviews((string)$dateSort ?: '',
                    (string)$rateSort ?: '',
                    (!empty($_GET['page']) && is_numeric($_GET['page'])) ? (int)$_GET['page'] : 1,
                    (!empty($_GET['limit']) && is_numeric($_GET['limit'])) ? (int)$_GET['limit'] : 10);
                $response['success'] = true;
                exit(json_encode($response));
            } catch (Exception $e) {
                header('HTTP/1.1 404 Not Found');
                exit(json_encode(['success' => false, 'message' => $e->getMessage()]));
            }
        }
        break;
    case 'PUT':
        $input_json = file_get_contents("php://input");
        $_POST = json_decode($input_json, true);

        $refs = explode('&', $_POST['refs']);
        if (count($refs) > 3)
            exit(json_encode(['success' => false, 'message' => 'Maximum refs is 3']));

        try {
            $response = $DB->createReview($_POST['name'], $_POST['review'], $_POST['rate'], $_POST['refs']);
            exit(json_encode(['success' => true, 'id' => $response]));
        } catch (Exception $e) {
            header('HTTP/1.1 500 Internal Server Error');
            exit(json_encode(['success' => false, 'message' => $e->getMessage()]));
        }
        break;
    default:
        header('HTTP/1.1 404 Not Found');
        exit();
        break;
}