<p>composer install</p>
<p>php artisan october:up</p>

<p align="center">
    <b>Application needs installed SphinxTrain, SphinxBase, PocketSphinx and lastest acoustic model!</b>
</p>
<p>
    Download all neccessary files:
    <a href="https://sourceforge.net/projects/cmusphinx/files/">CMUSphinx files</a>
</p>
<p>
    CMUSphinx model adaptation info:
    <a href="https://cmusphinx.github.io/wiki/tutorialadapt/">Documentation</a>
</p>
<p>If You have <i>Sphinx is not installed!</i> error when calls API's <i>generate_acoustic_model</i> method, check existing of <i>/usr/lib/sphinxtrain</i> folder</p>

<b>Installing LAPACK</b>:
<br/>
`sudo apt-get install libblas-dev liblapack-dev`

<b>Installing Sphinxbase</b>:<br/>
https://github.com/cmusphinx/sphinxbase

<b>Installing Pocketsphinx</b>:<br/>
https://github.com/cmusphinx/pocketsphinx

<b>Download, extract and replace files in archive</b>:<br/>
https://sourceforge.net/projects/cmusphinx/files/Acoustic%20and%20Language%20Models/US%20English/
<br/>
`cmusphinx-en-us-5.2.tar.gz -> /usr/local/share/pocketsphinx/model/en-us/en-us`

<b>Installing Sphinxtrain</b>:<br/>
https://github.com/cmusphinx/sphinxtrain
<br/>
`sudo apt-get install sphinxtrain`

`sudo ldconfig`

.env file:
<br/>
`TRAIN_PATH=/usr/local/libexec/sphinxtrain`

<b>Download, extract and follow the instruction. After installing copy all from `bin` to `/usr/bin`</b>:</br>
http://www.speech.cs.cmu.edu/SLM/toolkit_documentation.html#text2idngram# tiktalk_regression
