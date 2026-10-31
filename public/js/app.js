/* Shared progressive enhancements. All mutations remain protected server-side. */
$.ajaxSetup({headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Accept':'application/json'}});
window.showMessage=function(message,type='danger'){$('#client-message').removeClass('d-none alert-success alert-danger alert-info').addClass('alert-'+type).text(message).trigger('focus');};
window.requestError=function(xhr){return ({401:'Your session expired. Refresh and log in again.',419:'Your session expired. Refresh the page before trying again.',403:'You do not have permission to perform this action.',404:'This product no longer exists. Refresh the table.',422:'Some details are invalid. Please refresh and try again.',429:'Too many requests. Please wait and try again.'})[xhr.status]||'The request failed. Please try again.';};
$(document).on('submit','.js-confirm-delete',function(e){if(!window.confirm('Delete this product and its image permanently?')){e.preventDefault();}});
$(document).on('submit','.js-lock-submit',function(){$(this).find('button[type="submit"]').prop('disabled',true);});
