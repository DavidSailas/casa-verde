Menu book page-flip fix
-----------------------
1. Unzip this into your casa-verde project folder (the one containing app/, public/, resources/).
   Say "Replace" when asked. It changes only 3 files:
     resources/js/menubook.js                      (source)
     public/build/assets/menubook-Cascade1x.js     (compiled copy the site loads)
     public/build/manifest.json                    (points to the new file)
2. If you run "npm run dev", stop it and start it again.
3. Hard refresh the dining page: Ctrl + Shift + R.
