/**
 * Created by Tim  Jabs on 15.04.16.
 * Creates PDFs with a given ppn.
 *
 * This Libary needs following dependencies:
 * FileSaver.js
 * jspdf.debug.js
 * and jQuery
 *
 * The package manipulates the frontend of the digitalisation and is displayed by a modal dialog.
 * TODO: Make this libary more independend. Freedom!
 */

DCPDF = {};
DCPDF.base64images = [];
DCPDF.base64data = [];
DCPDF.internalOptions = [];

/**
 * @summary Show an error message in the progress gui for pdf-generation.
 * @param errorText "Alarm alarm"
 * @param isInProgress Boolean, if you want to show the error-message during the process or in the choosing phase.
 */
DCPDF.showError = function (errorText, isInProgress) {
    if (!isInProgress) {
        var $alert = jQuery('.alert');

        if ($alert.length) {
            $alert.replaceWith('<div class="alert alert-danger" style="display: none">' + errorText + '</div>');
            $alert.fadeIn();
        } else {
            jQuery('.choose-inner').prepend('<div class="alert alert-danger" style="display: none">' + errorText + '</div>');
            $alert.fadeIn();
        }
    } else {
        jQuery('.modal-body').prepend('<div class="alert alert-danger" style="display: none">' + errorText + '</div>');
        jQuery('.alert').fadeIn();
        DCPDF.setProgress("100%");
        jQuery('.progress-bar').removeClass('progress-bar-sbb');
        jQuery('.progress-bar').addClass('progress-bar-danger');
        jQuery('.pdf-generation-button').hide();
        jQuery('.pdf-close').show();
    }
};


DCGlobals = {};
DCGlobals.getWork = function() {

    var doc = {};
    doc.structure = {
        pages_length: 1
    }

    return doc;
};

/**
 * @summary Resets the Dialog and calculates the begin and start of the selected Section.
 * @param ppn like PPN13204823
 * @param physIDstart Work start
 * @param physIDend Work end
 */
DCPDF.resetGUI = function (ppn, physIDstart, physIDend) {
    jQuery('#pdf-range').remove();
    jQuery(".pdf-generation-button").text('pdf_progress_gen');
    if (physIDstart || physIDend) {
        jQuery("input[name='physIDstart']").val(DCPDF.extractPhysIDs(physIDstart));
        jQuery("input[name='physIDend']").val(DCPDF.extractPhysIDs(physIDend));
        DCPDF.calculatePDFGenerationStatistics(DCPDF.extractPhysIDs(physIDstart), DCPDF.extractPhysIDs(physIDend));
    } else {
        jQuery("input[name='physIDstart']").val(1);
        jQuery("input[name='physIDend']").val(DCGlobals.getWork().structure.pages_length);
        DCPDF.calculatePDFGenerationStatistics(1, DCGlobals.getWork().structure.pages_length);
    }
    jQuery('.progress-wrapper').css('display', 'none');
    jQuery('.choose-inner').fadeIn();
    jQuery(".progress-bar").attr("aria-valuenow", 0);
    DCPDF.setProgress(0);
    jQuery('.pdf-generation-button').prop('disabled', false);
    jQuery('.alert').remove();
    jQuery('.progress-bar').removeClass('progress-bar-danger').addClass('active');
    jQuery('.pdf-generation-button').show();
    jQuery('.modal-title').text('pdf_headline');
    jQuery('.pdf-close').hide();
    jQuery().removeClass('progress-bar-danger');
};

/**
 * @summary Switches to progress view after choosing options.
 */
DCPDF.showProgress = function () {
    jQuery(".choose-inner").fadeOut(function () {
        jQuery(".progress-wrapper").fadeIn();
    });
    jQuery(".modal-title").fadeOut(function () {
        jQuery(".modal-title").text('pdf_progress_headline');
        jQuery(".modal-title").fadeIn();
    });
};

/**
 * @summary Set the maximum value for the progress bar.
 * @param maxVal
 */
DCPDF.setMaxValueForProgress = function (maxVal) {
    jQuery(".progress-bar").attr("aria-valuemax", maxVal);
};

/**
 * @summary Set progress state for the progressbar.
 * @param progressVal
 */
DCPDF.setProgress = function (progressVal) {

    var progressBar = jQuery(".progress-bar");
    progressBar.attr("aria-valuenow", progressVal);
    var percentage = Math.floor((parseInt(progressVal) / parseInt(progressBar.attr("aria-valuemax"))) * 100) + "%";
    progressBar.css("width", percentage);
    progressBar.text(percentage);
    if (percentage === "100%") {
        jQuery(".progress-bar").removeClass("active");
    }
    if (progressVal === "100%") {
        progressBar.removeClass("active");
        progressBar.css("width", "100%");
        jQuery(".progress-text").text("100%");
    }
};

/**
 * @summary Return the progress of the progressbar.
 * @returns {Number}
 */
DCPDF.getProgress = function () {
    return parseInt(jQuery(".progress-bar").attr("aria-valuenow"));
};

/**
 * @summary Hide progressbar.
 */
DCPDF.hideProgressbar = function () {
    jQuery(".progress-wrapper").fadeOut();
};

//
/**
 * @summary Return a new promise for adding it later to an array of promises. Images will be loaded and returned as data-url.
 * @param path URL of image
 */
DCPDF.preloadImage = function (path) {
    return new Promise(function (resolve, reject) {
        // Create a new image from JavaScript
        var image = new Image();
        image.setAttribute('crossOrigin', 'anonymous');

        // Bind an event listener on the load to call the `resolve` function
        // FROM http://stackoverflow.com/questions/934012/get-image-data-in-javascript
        image.onload = function () {
            var canvas = document.createElement('canvas');
            canvas.width = this.width;
            canvas.height = this.height;
            var context = canvas.getContext('2d');
            context.drawImage(this, 0, 0);
            dataURL = canvas.toDataURL('image/jpeg');
            DCPDF.base64data.push({
                dataURL: dataURL,
                phys_id: parseInt(this.src.substr(this.src.length - 8)),
                width: this.width,
                height: this.height
            });
            DCPDF.setProgress(DCPDF.getProgress() + 1);
            resolve();
        };
        // If the image fails to be downloaded, we don't want the whole system
        // to collapse so we `resolve` instead of `reject`, even on error
        image.onerror = resolve;
        // Apply the path as `src` to the image so that the browser fetches it
        image.src = path;
    });
};

/**
 * @summary Handles the loading process for images in the pdf and adds them after successful loading into the pdf.
 * @param ppn Like PPN123456789
 */
DCPDF.preload = function (ppn) {
    // Promises are not supported, let's leave
    if (!('Promise' in window)) {
        return;
    }
    DCPDF.base64data = [];
    // Replace the image path with a promise that will resolve when the image
    // gets downloaded by the browser.
    _.each(DCPDF.base64images, function (item, key) {
        if (typeof item !== undefined) {
            DCPDF.base64images[key] = DCPDF.preloadImage(item);
        }
    });
    // When all images have been fetched...
    Promise.all(DCPDF.base64images).then(function () {
        // ...execute the callback
        DCPDF.buildPDF(ppn);
    }).catch(function (err) {
        // ...or log the error.
        DCPDF.showError('pdf_error_general');
        console.log(err);
    });
};

/**
 * Build an array with urls assembled from the given phys_ids. Need a list of physIDs to assemble the right ngcs(Content Server) url.
 * @param phys_id_list Like [PHYS_0001, PHYS_0002]
 * @param url Like http://digital-test.sbb.spk-berlin.de:83/
 * @param ppn Like PPN123456789
 * @param picturewidth Defines the rendered picture-width of every pic. The height is accordingly calculated to the width.
 */
DCPDF.buildImageBase64Arr = function (phys_id_list, url, ppn, picturewidth) {
    _.each(phys_id_list, function (phys_id) {
        DCPDF.base64images.push(url + ppn + "/full/full/0/default.jpg");
    });
    DCPDF.setMaxValueForProgress(phys_id_list.length);
    DCPDF.preload(ppn);
};

/**
 * Add metadata to the pdf (first page).
 * @param doc A jsPDf-Object
 * @param ppn Like PPN123456789
 * @returns {*} the doc Object with the metadata.
 */
DCPDF.addMetadata = function (doc, ppn) {
    // PPN: PPN777874989
    // PURL: http://resolver.staatsbibliothek-berlin.de/SBB00014EFF00100000
    // Titel: Kriegs-Mitteilungen der Kunstgewerbeschule zu Barmen
    // Erscheinungsjahr: 1917
    // Signatur: 4"Krieg 1914/25379-16.1917
    // Kategorie: Krieg 1914-1918,Historische Drucke
    // Projekt: Schützengraben- und Feldzeitungen, Heimatgrüße des 1. Weltkriegs
    // Strukturtyp: periodical_volume
    // Anzahl gescannter Seiten: 9
    // Lizenz: CC BY-NC-SA 3.0
    var pictureOfSBBLogoAsDataURL = "data:image/png;base64,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";
    var rowSpace = 0;
    var y = 0;
    var x = 0;
    var wrapSize = 0;
    var headlineSize = 0;
    var fontSize = 0;
    var linesize = 0;
    var input_choice = jQuery("input:radio[name=optradio]:checked").val();
    var input_size = 1000;
    var format, divisor;

    // For input 'choose-free' get the given input size.
    if (input_choice == 'choose-free') {
        input_size = parseInt(jQuery("#choose-free").val(), 10);
    }
    if (input_choice == '1000px' || input_choice == 'choose-free') {
        // Calculate the barely input size in milli meter.
        var input_mm = input_size / 4.0;
        format = {"dcpdf-format": [input_mm * 1.41, input_mm]};
        divisor = 23 / (input_size / 100.0);
    } else {
        // For original scans use a4 as format.
        format = 'a4';
        divisor = 1;
    }

    doc = new jsPDF("p", "mm", format);
    rowSpace = 10 / divisor;
    y = 45 / divisor;
    x = 20 / divisor;
    gap = 40 / divisor;
    wrapSize = 160 / divisor;
    fontSize = 12 / divisor;
    headlineSize = 18 / divisor;
    linesize = 195 / divisor;
    // static SBB Logo as DataURL
    doc.addImage(
        pictureOfSBBLogoAsDataURL,
        'JPEG',
        15 / divisor,
        15 / divisor,
        53 / divisor,
        17 / divisor
    );
    var work = DCGlobals.getWork();
    doc.setFontSize(headlineSize);
    var splittedHeadlineTitle = doc.splitTextToSize(work.title || '', wrapSize);
    for (var index = 0; index < splittedHeadlineTitle.length; index++) {
        if (index < 7) {
            doc.text(x, y, splittedHeadlineTitle[index]);
            if (index != splittedHeadlineTitle.length - 1) {
                y += rowSpace;
            }
        } else {
            doc.text(x, y, splittedHeadlineTitle[index] + " ...");
            break;
        }
    }

    doc.setLineWidth(gap / 40.0);
    doc.line(x, y + gap / 8.0, linesize, y + gap / 8.0);
    y += rowSpace;
    y += rowSpace;

    doc.setFontSize(fontSize);
    doc.text(x, y, 'Vollständiger');
    doc.text(x, y + gap / 8.0, 'Titel:');
    var title = work.title;
    if ('subtitle' in work && work.subtitle !== '') {
        title += ': ' + work.subtitle;
    }
    _.each(doc.splitTextToSize(title, wrapSize - gap / 2.0), function (splitItem, index) {
        doc.text(x + gap, y + gap / 8.0, splitItem);
        if (index != title.length - 1) {
            y += rowSpace - gap / 7.0;
        }
    });
    y += rowSpace;
    doc.text(x, y, 'PPN:');
    doc.text(x + gap, y, ppn);
    y += rowSpace;
    doc.text(x, y, 'PURL:');
    doc.setTextColor(0, 0, 255);
    doc.text(x + gap, y, work.purl);
    doc.setTextColor(0, 0, 0);
    y += rowSpace;
    if (work.date_issued) {
        doc.text(x, y, 'Erscheinungsjahr:');
        doc.text(x + gap, y, work.date_issued);
        y += rowSpace;
    }
    doc.text(x, y, 'Signatur:');
    doc.text(x + gap, y, work.signature);
    y += rowSpace;
    doc.text(x, y, 'Kategorie(n):');
    var category = doc.splitTextToSize(work.categories.join(', '), wrapSize - gap / 2.0);
    _.each(category, function (splitItem, index) {
        doc.text(x + gap, y, splitItem);
        if (index != category.length - 1) {
            y += rowSpace - gap / 7.0;
        }
    });
    if ('related_items' in work) {
        var related_items = [];
        for (var id in work.related_items) {
            var item = work.related_items[id];
            if ('title' in item) {
                related_items.push(item.title);
            }
        }
        if (related_items.length > 0) {
            y += rowSpace;
            doc.text(x, y, 'Projekt:');
            var project = doc.splitTextToSize(related_items.join(', '), wrapSize - gap / 2);
            _.each(project, function (splitItem, index) {
                doc.text(x + gap, y, splitItem);
                if (index != project.length - 1) {
                    y += rowSpace - gap / 7.0;
                }
            });
        }
    }
    y += rowSpace;
    doc.text(x, y, 'Strukturtyp:');
    doc.text(x + gap, y, work.structure.type);
    y += rowSpace;
    doc.text(x, y, 'Seiten (gesamt):');
    doc.text(x + gap, y, work.structure.pages.length.toString());
    y += rowSpace;
    doc.text(x, y, 'Seiten (ausgewählt):');
    if (DCPDF.internalOptions.physIDstart == DCPDF.internalOptions.physIDend) {
        doc.text(x + gap, y, "" + DCPDF.extractPhysIDs(DCPDF.internalOptions.physIDstart));
    } else if (DCPDF.internalOptions.physIDstart || DCPDF.internalOptions.physIDend) {
        doc.text(x + gap, y, DCPDF.extractPhysIDs(DCPDF.internalOptions.physIDstart) + "-" + DCPDF.extractPhysIDs(DCPDF.internalOptions.physIDend));
    } else {
        doc.text(x + gap, y, "1-" + work.structure.pages.length.toString());
    }
    y += rowSpace;
    if (work.license) {
        doc.text(x, y, 'Lizenz:');
        doc.text(x + gap, y, work.license);
        y += rowSpace;
    }
    return doc;
};

/**
 * @summary Main function for the pdf-generation using jsPDF. DCPDF.base64data contains data-urls which will be inserted.
 * @param ppn like PPN1234567890
 */
DCPDF.buildPDF = function (ppn) {
    var doc = new jsPDF("p", "mm", "a4");
    //doc = DCPDF.addMetadata(doc, ppn);
    var count = 0;
    var data_length = DCPDF.base64data.length;
    _.each(DCPDF.base64data, function (item) {
        count++;
        if (DCPDF.internalOptions.width === 0 || DCPDF.internalOptions.height === 0) {
            doc.addPage();
            doc.addImage(
                item.dataURL,
                DCPDF.internalOptions.format,
                DCPDF.internalOptions.x,
                DCPDF.internalOptions.y,
                210,
                297
            );
        } else if (DCPDF.internalOptions.width === -1 || DCPDF.internalOptions.height === -1) {
            var itemWidthInMM = item.width * 25.4 / 300;
            var itemHeightInMM = item.height * 25.4 / 300;
            doc.addPage(itemWidthInMM, itemHeightInMM);
            doc.addImage(
                item.dataURL,
                DCPDF.internalOptions.format,
                DCPDF.internalOptions.x,
                DCPDF.internalOptions.y,
                itemWidthInMM,
                itemHeightInMM
            );
        } else {
            doc.addPage();
            doc.addImage(
                item.dataURL,
                DCPDF.internalOptions.format,
                DCPDF.internalOptions.x,
                DCPDF.internalOptions.y,
                DCPDF.internalOptions.width,
                DCPDF.internalOptions.height
            );
        }
    });
    try {
        doc.save(ppn + '.pdf');
        setTimeout(function () {
            jQuery('#PDFprogress').modal('hide');
        }, 2000);
    } catch (e) {
        console.log('error ' + e);
        DCPDF.showError('pdf_error_too_big');
    }
};

/**
 * @summary Function which is called from outside (template.js etc.). This function needs configuration-object like:
 * {
 *    url: "http://digital-test.sbb.spk-berlin.de:83/"
 *    width: 0, //of picutures
 *    height: 0, //of pictures
 *    x: 0, //position x
 *    y: 0, //position y
 *    format: 'JPEG', //picture format
 *    picturewidth: '1000', //picture width in px which are rendered from the ngcs (Content Server).
 *    physIDstart: 2,
 *    physIDend: 5
 * } and the PPN of the work.
 * @param ppn
 * @param pictureFormatOptions
 */
DCPDF.generatePDF = function (ppn, pictureFormatOptions) {
    DCPDF.showProgress();
    if (typeof(pictureFormatOptions) !== "undefined" && pictureFormatOptions) {
        DCPDF.internalOptions = pictureFormatOptions;
    } else {
        DCPDF.internalOptions = {
            url: 'http://localhost:3000/image/',
            width: 0,
            height: 0,
            x: 0,
            y: 0,
            format: 'JPEG',
            picturewidth: '',
            physIDstart: 1,
            physIDend: 1
        };
    }
    var physIDList = DCPDF.turnPageNumbersIntoPhysIDArray(DCPDF.internalOptions.physIDstart, DCPDF.internalOptions.physIDend);
    DCPDF.buildImageBase64Arr(physIDList, DCPDF.internalOptions.url, ppn, DCPDF.internalOptions.picturewidth);
};

/**
 * @summary Show the modal dialog from bootstrap.
 * @param ppn like PPN 0123456789
 * @param physIDstart Like PHYS_0001
 * @param physIDend Like PHYS_0003
 *
 */
DCPDF.showModal = function (ppn, physIDstart, physIDend) {

    var progressContainer = jQuery('#PDFprogress');
    DCPDF.resetGUI(ppn, physIDstart, physIDend);
    progressContainer.modal({
        backdrop: 'static',
        keyboard: false
    });
    progressContainer.modal('show');
    jQuery(".pdf-generation-button").focus();
};

/**
 * @summary Helper function to repeat a string given times.
 * @param str Like "marry "
 * @param times Like "3"
 * @returns {string} Result is "marry marry marry"
 */
DCPDF.repeat = function (str, times) {
    return new Array(times + 1).join(str);
};

/**
 * @summary Helper function to build a PHYSID from a string like "23" to "PHYS_0023"
 * @param theNumberOfPhysID Like 23
 * @returns {string} Like "PHYS_0023"
 */
DCPDF.buildPhysIDs = function (theNumberOfPhysID) {
    return "PHYS_" + (DCPDF.repeat("0", 4 - theNumberOfPhysID.toString().length)) + theNumberOfPhysID;
};

/**
 * @summary Extract a number from a string like "PHYS_0001" to "1"
 * @param physid Like "PHYS_0001"
 * @returns {Number} Like "1"
 */
DCPDF.extractPhysIDs = function (physid) {
    var numbersOfPhysID = physid.substring(physid.length - 4);
    return parseInt(numbersOfPhysID);
};

/**
 * @summary Turn page numbers like 20-23 to an array with PHYSIDs like [PHYS_0020, PHYS_0021, PHYS_0022, PHYS_0023].
 * @param scope First page number
 * @param scopeEnd Last page number
 * @returns {*}
 */
DCPDF.turnPageNumbersIntoPhysIDArray = function (scope, scopeEnd) {
    var outputArrayWithPhysIDs = [];
    if (typeof scopeEnd !== 'undefined' && scopeEnd) {
        if (parseInt(scopeEnd) > parseInt(scope)) {
            for (var i = parseInt(scope); i < parseInt(scopeEnd) + 1; ++i) {
                outputArrayWithPhysIDs.push(DCPDF.buildPhysIDs(i));
            }
        } else if (parseInt(scopeEnd) < parseInt(scope)) {
            console.log("Scope start is bigger than scope end or equal. Please fix your parameters.");
            return null;
        } else if (parseInt(scopeEnd) == parseInt(scope)) {
            outputArrayWithPhysIDs.push(DCPDF.buildPhysIDs(scope));
        }
    } else {
        for (var i = 1; i < parseInt(scope) + 1; i++) {
            outputArrayWithPhysIDs.push(DCPDF.buildPhysIDs(i));
        }
    }
    return outputArrayWithPhysIDs;
};

/**
 * @summary Calculate the size of a PDF and insert the result to the pdf-generation button.
 * @param pageStart First selected page
 * @param pageEnd Last selected page
 */
DCPDF.calculatePDFGenerationStatistics = function (pageStart, pageEnd) {
    if (pageStart != "" && pageStart != "undefined" && !isNaN(pageStart) && pageEnd != "" && pageEnd != "undefined" && !isNaN(pageEnd)) {
        pageEnd = parseInt(pageEnd);
        pageStart = parseInt(pageStart);
        var result = Math.abs(Math.abs(pageEnd) - Math.abs(pageStart)) + 1;
        if (!isNaN(result)) {
            var calculatedSize = 0;
            switch (jQuery("input:radio[name=optradio]:checked").val()) {
                case "1000px":
                    calculatedSize = 0.21 * result;
                    break;
                case "original":
                    calculatedSize = 1.4 * result;
                    break;
                case "choose-free":
                    var chooseFreeValue = jQuery("#choose-free").val();
                    var factor = chooseFreeValue / 1000.0;
                    calculatedSize = 0.21 * factor * result;
                    break;
                default:
                    calculatedSize = 0;
            }
            calculatedSize = Math.round(calculatedSize);
            if (calculatedSize === 0) {
                calculatedSize = "> 1";
            }
            if (result === 1 && calculatedSize === -1) {
                jQuery(".pdf-generation-button").text('pdf_button_onePage');
            } else if (result === 1 && calculatedSize !== -1) {
                jQuery(".pdf-generation-button").text('pdf_button_onePage_size'  + calculatedSize + " MB");
            } else if (result !== 1 && calculatedSize === -1) {
                jQuery(".pdf-generation-button").text('pdf_button_size_part1' + result + 'pdf_button_size_part2');
            } else {
                jQuery(".pdf-generation-button").text('pdf_button_size_part1' + result + 'pdf_button_size_part2' + " (ca. " + calculatedSize + " MB");
            }
        } else {
            jQuery(".pdf-generation-button").text('pdf_progress_gen');
        }
    } else {
        jQuery(".pdf-generation-button").text('pdf_progress_gen');
    }
};
