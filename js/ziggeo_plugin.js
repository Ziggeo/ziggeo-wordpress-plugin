//in case there are multiple walls on the same page, we want to be sure not to cause issues. This should catch it and not declare the function again.                    
if(typeof ziggeoShowVideoWall !== 'function') {                        
    //show video wall based on its ID
    function ziggeoShowVideoWall(id) {
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

        //HTML output buffer
        var html = '';

        //set the video wall title
        html += ZiggeoWall[id].title;

        //To show the page we must first index videos..
        ZiggeoApi.Videos.index(ZiggeoWall[id].tags, {
            success: function (args, data) {
                if(data.length > 0) {
                    //we got some videos back
                    //go through videos

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
                        if(ZiggeoWall[id].indexing.status === 'all') {
                            tmp += tmp_embedding;
                            usedVideos++;
                            currentVideosPageCount++;
                        }
                        //show only rejected videos
                        else if(ZiggeoWall[id].indexing.status === 'rejected') {
                           if(data[i].approved === false) {
                                tmp += tmp_embedding;
                                usedVideos++;
                                currentVideosPageCount++;
                           }
                        }
                        //show only pending videos
                        else if(ZiggeoWall[id].indexing.status === 'pending') {
                           if(data[i].approved === null || data[i].approved === '' ) {
                                tmp += tmp_embedding;
                                usedVideos++;
                                currentVideosPageCount++;
                           }
                        }
                        //show approved videos 
                        else {
                            if(data[i].approved === true) {
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
                }
                else {
                    //no results
                    //follow the procedure for no videos (on no videos)
                    console.log('No videos found matching the requested:' + args);

                    //Lets process no videos which will return false or built HTML code.
                    html = ziggeoWallHandleNoVideos(id, html);
                    //function returns false if it should break out from the possition call was made.
                    if(html === false) { return false; }
                }

                //Lets add everything so that it is shown..
                wall.innerHTML = html;
               
                //lets show it:
                wall.style.display = 'block';
            },
            falure: function (args, error) {
                console.log('This was the error that we got back when seaching for ' + args +  ':' + error);
            }
        });
    }
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