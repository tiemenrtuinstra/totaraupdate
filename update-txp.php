<?php
/***
 * Hoe te gebruiken:
 * 1. Plaats deze php file in een mapje.
 * 2. Maak een submapje 'zips' aan. Zet daar de update tar.gz files in zoals die vanaf totara gedownload kunnen worden.
 * 3. Doe een `git checkout git@git.brightalley.nl:bright-alley/totara.git totara-sources' zodat er een submapje ontstaat met onze totara repo.
 * 4. `php extractupdates.php`, hij gaat even pruttelen en je bent klaar.
 */

$versionmap = [
    'totaratxp-13.??.tar.gz' => 'totara-txp',
    'totaratxp-14.?.tar.gz' => 'T14',
];

$zipdir = __DIR__.'/zips/';
$srcdir = __DIR__.'/totara-sources-txp/';
$versionregex = '/(\d?\d)\.(\d?\d)(.\d\d)?/';

if(!is_dir($zipdir) || !is_dir($srcdir)) {
    echo "Make sure the zipdir {$zipdir} exists and the totara repo https://git.brightalley.nl/bright-alley/totara-txp/ is checked out to {$srcdir}.";
    die();
}

foreach($versionmap as $filemask => $branch) {
    $zipfile = glob($zipdir.$filemask);
    if(!count($zipfile)) {
        echo "Zipfile {$filemask} for update {$branch} not found.\n";
        continue;
    }
    $zipfile = $zipfile[0];

    /* Extract version from filename */
    $tag = basename($zipfile);
    preg_match($versionregex, $tag, $matches);
    $tag = 'v'.$matches[0];

    echo "[{$branch}] Found version {$tag}\n";

    /* Update repo and clean up files */
    echo "[{$branch}] Updating repo..\n";
    exec("cd '{$srcdir}'; git checkout '{$branch}'; git pull; rm * -rf");
    /* Extract the tar.gz to the folder */
    echo "[{$branch}] Extracting...\n";
    exec("cd '{$srcdir}'; tar xf '{$zipfile}' --strip-components=1 -C ./");
    /* Commit with tag in comment */
    echo "[{$branch}] Committing...\n";
    exec("cd '{$srcdir}'; git add .; git commit -m '{$tag}'; git push");
    /* Tag the latest commit and push */
    echo "[{$branch}] Tagging...\n";
    exec("cd '{$srcdir}'; git tag '{$tag}'; git push --tags");
    echo "[{$branch}] DONE!\n";
}
