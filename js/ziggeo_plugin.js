//in case there are multiple walls on the same page, we want to be sure not to cause issues. This should catch it and not declare the function again.                    
if(typeof ziggeoShowVideoWall !== 'function') {
    //show video wall based on its ID
    function ziggeoShowVideoWall(id, searchParams) {

        if(searchParams === undefined || searchParams === "undefined" || searchParams === null || typeof(searchParams) != "string") {
            searchParams = "";
        }

        //reference to wall
        var wall = document.getElementById(id);

        //lets check if wall is existing or not. If not, we break out and report it.
        if(!wall) {
            console.log('Exiting function. Specified wall is not present');
            return false;
        }

        if(!ZiggeoWall[id]) {
            console.log('Wall found, however no data found for the same');
            return false;
        }

        var wallClass = (ZiggeoWall[id].indexing.showPages) ? 'ziggeo-wall-showPages' : (
            (ZiggeoWall[id].indexing.slideWall) ? 'ziggeo-wall-slideWall' : (
                (ZiggeoWall[id].indexing.chessboardGrid) ? 'ziggeo-wall-chessboardGrid' : (
                    (ZiggeoWall[id].indexing.mosaicGrid) ? 'ziggeo-wall-mosaicGrid' : ""
                )
            )
        );

        wall.className = "ziggeo_videoWall " + wallClass;

        //To show the page we must first index videos..

        //We are making it get 100 videos data per call
        ZiggeoApi.Videos.index( 'limit=100&tags='+ZiggeoWall[id].tags + searchParams, {
            success: function (args, data) {
                if(data.length > 0) {
                    //we got some videos back
                    //go through videos

                    //if showPages or slideWall are true, then we use the original walls with pages
                    if(ZiggeoWall[id].indexing.showPages || ZiggeoWall[id].indexing.slideWall) {

                        //HTML output buffer
                        var html = '';

                        //set the video wall title
                        html += ZiggeoWall[id].title;

                        handlePagedWalls(wall, id, html, data);
                    }
                    //if we are here, then this is one of the newer walls with endless scroll..
                    else {
                        //lets attach the event listener..
                        (document.addEventListener) ? (
                            window.addEventListener( 'scroll',  ziggeo_endlessScroll, false ) ) : (
                            window.attachEvent( 'onscroll', ziggeo_endlessScroll) );

                        if( ZiggeoWall['continueFrom'] ) {
                            ZiggeoWall['continueFrom'] += data.length;
                        }
                        else {
                            ZiggeoWall['continueFrom'] = data.length;
                        }

                        handleEndlessScrollWalls(wall, id, data, true);
                    }
                }
                else {
                    //no results
                    //follow the procedure for no videos (on no videos)
                    console.log('No videos found matching the requested:' + args);

                    //Lets process no videos which will return false or built HTML code.
                    var html = ziggeoWallHandleNoVideos(id, html);

                    //cancel the scrolling event when we have no more videos to load..
                    if(ZiggeoWall['endless'] === id) {
                        ZiggeoWall['endless'] = null;
                    }

                    var tmp = document.getElementById('ziggeo-endless-loading_more');

                    if(tmp) {
                        tmp.innerHTML = "No more videos..";
                    }

                    //function returns false if it should break out from the possition call was made.
                    if(html === false) { return false; }
                }

                ZiggeoWall[id].processing = false;
            },
            failure: function (args, error) {
                console.log('This was the error that we got back when searching for ' + args +  ':' + error);
            }
        });
    }
}
// function to handle the video walls without the pagination, having the endless scroll implementation base..
function handleEndlessScrollWalls(wall, id, data, _new) {

    //we need to create new page within the wall in order to not break anything..

    var html = wall;

    var usedVideos = 0;
    var j = data.length;
    
    if(ZiggeoWall['loadedData'] && _new === true) {
        j -= ZiggeoWall['loadedData'].length;
    }

    for(i = 0, tmp=''; i < j; i++, tmp='') {

        //break once we load enought of videos
        if(i >= ZiggeoWall[id].indexing.perPage) {
            break;
        }

        var tmp_embedding = '<ziggeo ' +
                        ' ziggeo-width=' + ZiggeoWall[id].videos.width +
                        ' ziggeo-height=' + ZiggeoWall[id].videos.height +
                        ' ziggeo-video="' + data[i].token + '"' +
                    '></ziggeo>';

        //show all videos
        if(ZiggeoWall[id].indexing.status.indexOf('all') > -1 ) {
            html.insertAdjacentHTML('beforeend', tmp_embedding);
            usedVideos++;
            data[i] = null;//so that it is not used by other ifs..
        }
        //show only rejected videos
        if(ZiggeoWall[id].indexing.status.indexOf('rejected') > -1 ) {
           if(data[i] !== null && data[i].approved === false) {
                html.insertAdjacentHTML('beforeend', tmp_embedding);
                usedVideos++;
                data[i] = null;//so that it is not used by other ifs..
           }
        }
        //show only pending videos
        if(ZiggeoWall[id].indexing.status.indexOf('pending') > -1 ) {
           if(data[i] !== null && (data[i].approved === null || data[i].approved === '') ) {
                html.insertAdjacentHTML('beforeend', tmp_embedding);
                usedVideos++;
                data[i] = null;//so that it is not used by other ifs..
           }
        }
        //show approved videos 
        if(ZiggeoWall[id].indexing.status === '' || ZiggeoWall[id].indexing.status.indexOf('approved') > -1 ) {
            if(data[i] !== null && data[i].approved === true) {
                html.insertAdjacentHTML('beforeend', tmp_embedding);
                usedVideos++;
            }
        }
    }

    var tmp = document.getElementById('ziggeo-endless-loading_more');

    if(tmp) {
        tmp.parentNode.removeChild(tmp);
    }
    else {
        var loadingElm = document.createElement('div');
        loadingElm.id = "ziggeo-endless-loading_more";
        loadingElm.innerHTML = "Loading More Videos..";
        //@HERE - make this string translatable for people using WPML.
        //It will have two strings - loading more and no more videos..
        wall.parentNode.appendChild(loadingElm, wall);
    }

    ZiggeoWall['endless'] = id;

    for(i = -1, j = data.length; i < j; j--) {
        //break once we load enought of videos
        if(data[j] === null) {
            data.splice(j, 1);
        }
    }

    if(data.length > 0) {
        ZiggeoWall['loadedData'] = data;
    }
}
//handler for the scroll event, so that we can do our stuff for the endless scroll templates
function ziggeo_endlessScroll() {

    var wall = null;

    //get reference to the wall..
    if( ZiggeoWall && ZiggeoWall['endless'] && (wall = document.getElementById(ZiggeoWall['endless'])) ) {
        //all good
        var id = ZiggeoWall['endless'];
    }
    else {
        //OK so there is obviously no wall. Instead of recreating the same check each time, lets clean up..
        (document.removeEventListener) ? (
            window.removeEventListener( 'scroll',  ziggeo_endlessScroll ) ) : (
            window.detachEvent( 'onscroll', ziggeo_endlessScroll) );
        return false;
    }

    //lets go out if we are already processing the same request and scroll happened again..
    if(ZiggeoWall[id].processing === true) {
		return false;
	}

    //lets check the position of the bottom of the video wall from the top of the screen and then, if the same is equal to or lower than 80% of our video wall, we need to do some new things
    if(wall.getBoundingClientRect().bottom <= ( wall.getBoundingClientRect().height * 0.20 )) {
		//lets lock the indexing to not be called more than once for same scroll action..
		ZiggeoWall[id].processing = true;

        //do we have more data than we need to show? if we do, lets show it right away, if not, we should load more data and show what we have as well..
        if(ZiggeoWall['loadedData'].length > ZiggeoWall[id].indexing.perPage) {
            //we use the data we already got from our servers
            handleEndlessScrollWalls(wall, id, ZiggeoWall['loadedData']);
			ZiggeoWall[id].processing = false;
        }
        else {
            //we are using any data that we already have and create a call to grab new ones as well.
            handleEndlessScrollWalls(wall, id, ZiggeoWall['loadedData']);
            ziggeoShowVideoWall(id, '&skip=' + ZiggeoWall['continueFrom']);
        }
    }
}
// function to handle the video walls with the pagination
function handlePagedWalls(wall, id, html, data) {

    //number of videos per page currently
    var currentVideosPageCount = 0;
    //total number of videos that will be shown
    var usedVideos = 0;
    //What page are we on?
    var currentPage = 0;
    //did any videos match the checks while listing them - so that we do not place multiple pages since the count stays on 0
    var newPage = true;

    for(i = 0, j = data.length, tmp=''; i < j; i++, tmp='') {
        
        var tmp_embedding = '<ziggeo ' +
                        ' ziggeo-width=' + ZiggeoWall[id].videos.width +
                        ' ziggeo-height=' + ZiggeoWall[id].videos.height +
                        ' ziggeo-video="' + data[i].token + '"' +
                        ( (usedVideos === 0 && ZiggeoWall[id].videos.autoplay) ? ' ziggeo-autoplay ' : '' ) +
                    '></ziggeo>';

        //show all videos
        if(ZiggeoWall[id].indexing.status.indexOf('all') > -1 ) {
            tmp += tmp_embedding;
            usedVideos++;
            currentVideosPageCount++;
            data[i] = null;//so that it is not used by other ifs..
        }
        //show only rejected videos
        if(ZiggeoWall[id].indexing.status.indexOf('rejected') > -1 ) {
           if(data[i] !== null && data[i].approved === false) {
                tmp += tmp_embedding;
                usedVideos++;
                currentVideosPageCount++;
                data[i] = null;//so that it is not used by other ifs..
           }
        }
        //show only pending videos
        if(ZiggeoWall[id].indexing.status.indexOf('pending') > -1 ) {
           if(data[i] !== null && (data[i].approved === null || data[i].approved === '') ) {
                tmp += tmp_embedding;
                usedVideos++;
                currentVideosPageCount++;
                data[i] = null;//so that it is not used by other ifs..
           }
        }
        //show approved videos 
        if(ZiggeoWall[id].indexing.status === '' || ZiggeoWall[id].indexing.status.indexOf('approved') > -1 ) {
            if(data[i] !== null && data[i].approved === true) {
                tmp += tmp_embedding;
                usedVideos++;
                currentVideosPageCount++;
            }
        }

        //Do we need to create a new page?
        //We only create new page if there were any videos to add, otherwise if 1 video per page is set, we would end up with empty pages when videos are not added..
        if(currentVideosPageCount === 1 && newPage === true) {
            //we do
            currentPage++;

            //For slidewall we add next right away..
            if(ZiggeoWall[id].indexing.slideWall) {
                if(currentPage > 1) {
                    html += '<div class="ziggeo_videowall_slide_next"  onclick="ziggeoShowWallPage(\'' + id + '\', ' + currentPage + ');"></div>';
                    html += '</div>';
                }
            }

            html += '<div id="' + id + '_page_' + currentPage + '" class="ziggeo_wallpage">';

            //For slidewall we add back right away as well
            if(ZiggeoWall[id].indexing.slideWall) {
                if(currentPage > 1) {
                    html += '<div class="ziggeo_videowall_slide_previous"  onclick="ziggeoShowWallPage(\'' + id + '\', ' + (currentPage-1) + ');"></div>';
                }
            }

            html += tmp;
            tmp = '';
            newPage = false;                                                    
        }

        //combining the code if any
        if(tmp !== '') {
            html += tmp;
        }

        //Do we have enough of vidoes on this page and its time to create a new one?
        if(currentVideosPageCount === ZiggeoWall[id].indexing.perPage) {
            //Yup, we do
            if(ZiggeoWall[id].indexing.showPages) {
                html += '</div>';                                                    
            }
            currentVideosPageCount = 0;
            newPage = true;
        }
    }

    //Sometimes we will have videos, however due to calling parameters the same might not be added.
    //At this time we would need to show the log in console about the same and show the on_no_videos message / setup
    if(usedVideos === 0 && i > 0) {
        html = ziggeoWallHandleNoVideos(id, html);

        //leaving a note of this
        console.log('You have videos, just not the ones matching your request');

        if(html === false) {
            return false;
        }
    }

    //In case last page has less videos than per page limit, we need to apply the closing tag
    if(currentVideosPageCount < ZiggeoWall[id].indexing.perPage && newPage === false) {
        html += '</div>';
    }

    //Lets add pages if showPages is set
    if(ZiggeoWall[id].indexing.showPages) {
        for(i = 0; i < currentPage; i++) {
            html += '<div class="ziggeo_wallpage_number' + ((i===0) ? ' current' : '') + '" onclick="ziggeoShowWallPage(\'' + id + '\', ' + (i+1) + ',this);">' + (i+1) + '</div>';
        }
        html += '<br class="clear" style="clear:both;">';
    }

    //Lets add everything so that it is shown..
    wall.innerHTML = html;

    //lets show it:
    wall.style.display = 'block';
}
//handler for the cases when either no videos are found or videos found do not match the status requested (not to be mistaken with 'video status').
function ziggeoWallHandleNoVideos(id, html) {

    //Is the vall set up to be hidden when there are no videos?
    if(ZiggeoWall[id].onNoVideos.hideWall) {
        //Lets still leave a note about it in console.
        console.log('VideoWall not shown');
        return false;
    }

    //adding page - has additional (empty) class to allow nicer styling
    html += '<div id="' + id + '_page_1' + '" class="ziggeo_wallpage empty">';

    //Should we show some template?
    if(ZiggeoWall[id].onNoVideos.showTemplate) {
        html += '<ziggeo ' + ZiggeoWall[id].onNoVideos.templateName + '></ziggeo>';
    }
    else { //or a message instead?
        html += ZiggeoWall[id].onNoVideos.message;
    }
    //closing the page.
    html += '</div>';

    return html; //return the code we built..
}
//Shows the selected page and hides the rest of the specific video wall.
function ziggeoShowWallPage(id, page, current) {
    //reference to wall
    var wall = document.getElementById(id);

    //lets check if wall is existing or not. If not, we break out and report it.
    if(!wall) {
        console.log('Exiting function. Specified wall is not present');
        return false;
    }

    var pageID = id + '_page_' + page;

    var newPage = document.getElementById(pageID);

    //Get all pages under current wall
    var pages = wall.getElementsByClassName('ziggeo_wallpage');

    //Hide all of the pages
    for(i = 0, j = pages.length; i < j; i++) {
        pages[i].style.display = 'none';
    }

    //set the visual indicator of what page is selected
    var pageNumbers = wall.getElementsByClassName('ziggeo_wallpage_number');

    //This is only active if we show page numbers / page buttons
    if(current) {
        //reset style of the page number buttons
        for(i = 0, j = pageNumbers.length; i < j; i++) {
            pageNumbers[i].className = 'ziggeo_wallpage_number';
        }

        //adding .current class to the existing list of classes
        current.className = 'ziggeo_wallpage_number current';
    }

    newPage.style.display = 'block';
}

//shows overlay with the recorder element
function ziggeoShowOverlayWithRecorder(z_tinyMCEEditor) {
    //create element covering entire screen
    var o = document.createElement('div');
    o.style.position= "fixed";
    o.style.top = 0;
    o.style.left = 0;
    o.style.width = "100%";
    o.style.height = "100%";
    o.style['background-color'] = "gray";
    o.style.opacity = 0.6;
    o.style.zIndex = 99999;
    o.id = "ziggeo-overlay-screen";
    document.body.appendChild(o);

    //lets create element that will close this..
    var c = document.createElement('div');
    c.style.width = "30px";
    c.style.height = "30px";
    c.style.position = "absolute";
    c.style.right = "10px";
    c.style.top = "10px";
    c.style['background-color'] = "white";
    c.style.color = "white";
    c.style.cursor = "pointer";
    c.style['text-shadow'] = "-1px -1px 0px black, 1px 1px 1px black, -1px 1px 1px black, 1px -1px 1px black";
    c.style['text-align'] = "center";
    c.style['border-radius'] = "50%";
    c.id="ziggeo-overlay-close";
    c.innerHTML = "x";
    c.addEventListener( 'click', ziggeoRemoveOverlayWithRecorder, false );
    document.getElementById('ziggeo-overlay-screen').appendChild(c);

    //now the element that will hold our recorder (we make sure that it will be fully displayed on mobile and desktop screens)..
    var s = document.createElement('div');
    s.id="ziggeo-video-screen";
    s.style.width = "300px"
    s.style.height = "300px";
    s.style['background-color'] = "white";
    s.style.left = "calc(50% - 150px)";
    s.style.top = "calc(50% - 150px)";
    s.style.position = "fixed";
    s.style.zIndex = "100000";
    document.body.appendChild(s);

    //create recorder using v1 recorder code
    ZiggeoApi.Embed.embed('#ziggeo-video-screen', {width: 300, height: 300, perms: ["allowupload"] } );

    //add event handler
    ZiggeoApi.Events.on("submitted", function ( data ) {
        //The record video button above the toolbar has its own code that reacts to can react to this one so lets hide it..
        jQuery("#revert-ziggeo-button").css("display", "none");
        jQuery("#accept-ziggeo-button").css("display", "none");
        //might be good to know what we will add the data to - which integration is needing it..
        z_tinyMCEEditor.insertContent( '[ziggeo]' + data.video.token + '[/ziggeo]' );
        //Since video is submitted, lets make sure that this is shown a bit differently - pointed out more..
        document.getElementById('ziggeo-overlay-close').style['background-color'] = "orangeRed";
    });
}

//destroys overlay and recorder over it
//TODO maybe make it possible to close only if the video was uploaded, for now, lets just close it.
function ziggeoRemoveOverlayWithRecorder() {
    jQuery("#ziggeo-video-screen").remove();
    jQuery("#ziggeo-overlay-screen").remove();
}