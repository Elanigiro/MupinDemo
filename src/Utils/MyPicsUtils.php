<?php
declare(strict_types=1);
namespace App\Utils;

use Nyholm\Psr7\UploadedFile;
use RuntimeException;

class MyPicsUtils {

    public const IMG_EXTENSIONS = ".jpg,.jpeg,.png,.gif";
    public const IMG_EXTENSIONS_ARRAY = ["jpg", "jpeg", "png", "gif"];
    public const IMG_LIMIT = 10;
    public const IMG_NAME_FORMATTING = "%s/%s_%02d%s";
    public const IMG_DEFAULT_BASE_DIR = __DIR__ . '/../../public/img/mupin';
    public const IMG_TMP_DIR = __DIR__ . '/../../tmp_item_pics';

    public static function getPicId(string $picDir): string {

        return preg_replace('/_\d+$/', '', pathinfo($picDir, PATHINFO_FILENAME));
    }

    public static function getPicParentDir(string $picDir): string {

        return pathinfo($picDir, PATHINFO_DIRNAME);
    }

    public static function getPicExtension(string $picDir, bool $dotted = true): string {

        $res = pathinfo($picDir, PATHINFO_EXTENSION);

        if ($dotted) {

            $res = ('.' . $res);
        }

        return $res;
    }

    public static function validPicExtension(UploadedFile $pic): string|false {

        $extension = self::getPicExtension($pic->getClientFilename(), dotted: false);

        if (in_array($extension, self::IMG_EXTENSIONS_ARRAY, strict: true)) {

            return $extension;
        }

        return false;
    }

    public static function findPicsById(string $itemId, string $dir = self::IMG_DEFAULT_BASE_DIR, bool $sorted = false): array {

        $res = (glob($dir . "/${itemId}_*{" . self::IMG_EXTENSIONS . "}", GLOB_BRACE)) ?: [];

        if ($sorted) {

            natsort($res);
        }

        return $res;
    }

    /** 
     * @param array $uploadedPics MUST be an array of validated UploadedFile objects
     * @param $startIdx MUST be between [0, IMG_LIMIT)
     * @throws RuntimeException in case of failure as long as $throwing === true
     * @warning you should pick the $startIdx carefully
     *  */
    public static function insertPics(array $uploadedPics, string $picDirectory, string $picId, int $startIdx, bool $throwing = true): bool {

        for ($i = 0; $i < count($uploadedPics); ++$i) {

            $currentPic = $uploadedPics[$i];
            $currentExtension = self::getPicExtension($currentPic->getClientFilename());
            try {
                $currentPic->moveTo(sprintf(self::IMG_NAME_FORMATTING, $picDirectory, $picId, $i + $startIdx, $currentExtension));
            } catch (RuntimeException $th) {
                if ($throwing) {

                    throw $th;
                }
                return false;
            }
        }

        return true;
    }

    /** @throws RuntimeException in case of failure as long as $throwing === true */
    public static function removePics(string $picId, string $picDirectory = self::IMG_DEFAULT_BASE_DIR, bool $throwing = true): bool {

        $foundPics = self::findPicsById($picId, $picDirectory);

        foreach ($foundPics as $fileName) {
            
            if (unlink($fileName) === false) {

                if ($throwing) {

                    throw new RuntimeException("$fileName could not be unlinked");
                }

                return false;
            }
        }

        return true;
    }

    /** @throws RuntimeException in case of failure as long as $throwing === true */
    public static function copyPics(string $picId, string $picSourceDir, string $picDestinationDir, bool $throwing = true): bool {

        $foundPics = self::findPicsById($picId, $picSourceDir);

        foreach ($foundPics as $fileName) {

            if (copy($fileName, $picDestinationDir . DIRECTORY_SEPARATOR . basename($fileName)) === false) {

                if ($throwing) {

                    throw new RuntimeException("$fileName could not be copied");
                }
                return false;
            }
        }
        return true;
    }

    public static function clearFolder(string $folderDir): void {

        array_map('unlink', glob("$folderDir/*")?: []);
    }

    /** @throws RuntimeException in case of failure as long as $throwing === true */
    protected static function refreshPicNames(string $picId, string $parentDir, bool $throwing = true): bool {

        $pics = self::findPicsById($picId, $parentDir, sorted: true);

        for ($i = 0; $i < count($pics); ++$i) {

            $currentPic = $pics[$i];         
            $extension = self::getPicExtension($currentPic);             
            if (rename($currentPic, sprintf(self::IMG_NAME_FORMATTING, $parentDir, $picId, $i, $extension)) === false) {

                if ($throwing) {

                    throw new RuntimeException("$currentPic could not be renamed");
                }
                return false;
            }
        }
        return true;
    }

    public static function getNextIdx(string $picId, string $parentDir): int {

        return count(self::findPicsById($picId, $parentDir));
    }

    /** @throws RuntimeException in case of failure as long as $throwing === true */
    public static function removeSpecificPic(string $picDir, bool $throwing = true): bool {

        $picId = self::getPicId($picDir);
        $parentDir = self::getPicParentDir($picDir);

        if (unlink($picDir) === false) {

            if ($throwing) {

                throw new RuntimeException("$picDir could not be unlinked");
            }

            return false;
        }

        return self::refreshPicNames($picId, $parentDir, $throwing);
    }
}