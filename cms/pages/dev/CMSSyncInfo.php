<?php 
namespace manguto\cms7\lib\cms\dev; 

class CMSSyncInfo 
{ 

	// ######################################################################################################################################### 
	// PASTAS OU ARQUIVOS DA ESTRUTURA DO CMS 
	// 'critic' => precisam estar sempre iguais com a base
	// 'critic' => resultado de modificacoes implementacionais
	// 'trash' => arquivos temporarios ou dispensaveis 
	// #########################################################################################################################################
	const cmsFileInfo_array = [
	//========================================== res > css >  
	'res/css/lib.css' => 'critic',  
	'res/css/print.css' => 'critic',  
	'res/css/style.css' => 'critic',  
			
	//========================================== res > fonts >  
	'res/fonts/FontAwesome.otf' => 'critic',  
	'res/fonts/fontawesome-webfont.eot' => 'critic',  
	'res/fonts/fontawesome-webfont.svg' => 'critic',  
	'res/fonts/fontawesome-webfont.ttf' => 'critic',  
	'res/fonts/fontawesome-webfont.woff' => 'critic',  
	'res/fonts/fonts.css' => 'critic',  
	'res/fonts/glyphicons-halflings-regular.eot' => 'critic',  
	'res/fonts/glyphicons-halflings-regular.svg' => 'critic',  
	'res/fonts/glyphicons-halflings-regular.ttf' => 'critic',  
	'res/fonts/glyphicons-halflings-regular.woff' => 'critic',  
	'res/fonts/index.php' => 'critic',  
			
	//=============================================================== res > fonts > Open_Sans >  
	'res/fonts/Open_Sans/LICENSE.txt' => 'critic',  
	'res/fonts/Open_Sans/OpenSans-Regular.ttf' => 'critic',  
			
	//=============================================================== res > fonts > Open_Sans_Condensed >  
	'res/fonts/Open_Sans_Condensed/LICENSE.txt' => 'critic',  
	'res/fonts/Open_Sans_Condensed/OpenSansCondensed-Light.ttf' => 'critic',  
			
	//========================================== res > img >  
	'res/img/back.png' => 'critic',  
	'res/img/favicon.png' => 'critic',  
	'res/img/home_16.png' => 'critic',  
	'res/img/home_20.png' => 'critic',  
	'res/img/icon_admin.png' => 'critic',  
	'res/img/icon_admin2.png' => 'critic',  
	'res/img/icon_dev.png' => 'critic',  
	'res/img/icon_dev2.png' => 'critic',  
	'res/img/icon_site.png' => 'critic',  
	'res/img/icon_site2.png' => 'critic',  
			
	//========================================== res > js >  
	'res/js/bootstrap-table.min.rar' => 'critic',  
	'res/js/bootstrap.min.rar' => 'critic',  
	'res/js/jquery.min.js' => 'critic',  
	'res/js/jquery.min.rar' => 'critic',  
	'res/js/lib.js' => 'critic',  
	'res/js/lib_form.js' => 'critic',  
	'res/js/popper.min.js' => 'critic',  
	'res/js/scripts.js' => 'critic',  
			
	//========================================== sis > control >  
	'sis/control/Control.php' => 'critic',  
			
	//=============================================================== sis > control > admin >  
	'sis/control/admin/ControlHome.php' => 'critic',  
	'sis/control/admin/ControlProfile.php' => 'critic',  
	'sis/control/admin/ControlUsers.php' => 'critic',  
	'sis/control/admin/ControlZzz.php' => 'critic',  
			
	//=============================================================== sis > control > crud >  
	'sis/control/crud/ControlZzz.php' => 'critic',  
			
	//=============================================================== sis > control > dev >  
	'sis/control/dev/ControlDocs.php' => 'critic',  
	'sis/control/dev/ControlHome.php' => 'critic',  
	'sis/control/dev/ControlLog.php' => 'critic',  
	'sis/control/dev/ControlManutencao.php' => 'critic',  
	'sis/control/dev/ControlModels.php' => 'critic',  
	'sis/control/dev/ControlModules.php' => 'critic',  
	'sis/control/dev/ControlRepository.php' => 'critic',  
	'sis/control/dev/ControlSync.php' => 'critic',  
	'sis/control/dev/ControlTools.php' => 'critic',  
	'sis/control/dev/ControlToolsCRUD.php' => 'critic',  
	'sis/control/dev/ControlToolsModules.php' => 'critic',  
	'sis/control/dev/ControlToolsRepository.php' => 'critic',  
	'sis/control/dev/ControlUsers.php' => 'critic',  
	'sis/control/dev/ControlZzz.php' => 'critic',  
			
	//=============================================================== sis > control > site >  
	'sis/control/site/ControlForgot.php' => 'critic',  
	'sis/control/site/ControlHome.php' => 'critic',  
	'sis/control/site/ControlLogin.php' => 'critic',  
	'sis/control/site/ControlProfile.php' => 'critic',  
	'sis/control/site/ControlRegister.php' => 'critic',  
	'sis/control/site/ControlZzz.php' => 'critic',  
			
	//========================================== sis > lib >  
	'sis/lib/Xxx.php' => 'critic',  
			
	//========================================== sis > model >  
	'sis/model/Manutencao.php' => 'critic',  
	'sis/model/Profile.php' => 'critic',  
	'sis/model/User.php' => 'critic',
	'sis/model/User_module.php' => 'critic',  
	'sis/model/User_password_recover.php' => 'critic',  
	'sis/model/User_profile.php' => 'critic',  
	'sis/model/Xxx.php' => 'critic',  
	'sis/model/Zzz.php' => 'critic',  
			
	//=============================================================== sis > tpl > admin >  
	'sis/tpl/admin/_menu.html' => 'critic',  
	'sis/tpl/admin/home.html' => 'critic',  
	'sis/tpl/admin/log.html' => 'critic',  
	'sis/tpl/admin/log_completo.html' => 'critic',  
	'sis/tpl/admin/log_data.html' => 'critic',  
	'sis/tpl/admin/login.html' => 'critic',  
	'sis/tpl/admin/profile-change-password.html' => 'critic',  
	'sis/tpl/admin/profile.html' => 'critic',  
	'sis/tpl/admin/users-create.html' => 'critic',  
	'sis/tpl/admin/users-update.html' => 'critic',  
	'sis/tpl/admin/users-view.html' => 'critic',  
	'sis/tpl/admin/users.html' => 'critic',  
	'sis/tpl/admin/zzz.html' => 'critic',  
			
	//=============================================================== sis > tpl > crud >  
	'sis/tpl/crud/zzz.html' => 'critic',  
	'sis/tpl/crud/zzz_edit.html' => 'critic',  
	'sis/tpl/crud/zzz_view.html' => 'critic',  
			
	//=============================================================== sis > tpl > dev >  
	'sis/tpl/dev/_menu.html' => 'critic',  
	'sis/tpl/dev/development.html' => 'critic',  
	'sis/tpl/dev/development_cms_checkbasefiles.html' => 'critic',  
	'sis/tpl/dev/docs.html' => 'critic',  
	'sis/tpl/dev/docs_page.html' => 'critic',  
	'sis/tpl/dev/index.html' => 'critic',  
	'sis/tpl/dev/informacoes.html' => 'critic',  
	'sis/tpl/dev/log_ano.html' => 'critic',  
	'sis/tpl/dev/log_dia.html' => 'critic',  
	'sis/tpl/dev/manutencao.html' => 'critic',  
	'sis/tpl/dev/models.html' => 'critic',  
	'sis/tpl/dev/modules.html' => 'critic',  
	'sis/tpl/dev/repository.html' => 'critic',  
	'sis/tpl/dev/repository_register_edit.html' => 'critic',  
	'sis/tpl/dev/repository_register_view.html' => 'critic',  
	'sis/tpl/dev/repository_sheet_view.html' => 'critic',  
	'sis/tpl/dev/repository_view.html' => 'critic',  
	'sis/tpl/dev/sync.html' => 'critic',  
	'sis/tpl/dev/sync_analyse.html' => 'critic',  
	'sis/tpl/dev/sync_files.html' => 'critic',  
	'sis/tpl/dev/sync_go.html' => 'critic',  
	'sis/tpl/dev/sync_go_script.html' => 'critic',  
	'sis/tpl/dev/sync_go_style.html' => 'critic',  
	'sis/tpl/dev/tools.html' => 'critic',  
	'sis/tpl/dev/tools_crud.html' => 'critic',  
	'sis/tpl/dev/tools_crud_model.html' => 'critic',  
	'sis/tpl/dev/tools_modules.html' => 'critic',  
	'sis/tpl/dev/tools_modules_result.html' => 'critic',  
	'sis/tpl/dev/tools_repository.html' => 'critic',  
	'sis/tpl/dev/users-create.html' => 'critic',  
	'sis/tpl/dev/users-update.html' => 'critic',  
	'sis/tpl/dev/users-view.html' => 'critic',  
	'sis/tpl/dev/users.html' => 'critic',  
	'sis/tpl/dev/zzz.html' => 'critic',  
			
	//=============================================================== sis > tpl > email >  
	'sis/tpl/email/email_forgot.html' => 'critic',  
	'sis/tpl/email/email_forgot_2019.html' => 'critic',  
			
	//=============================================================== sis > tpl > error >  
	'sis/tpl/error/notfound.html' => 'critic',  
			
	//=============================================================== sis > tpl > general >  
	'sis/tpl/general/__close.html' => 'critic',  
	'sis/tpl/general/__open.html' => 'critic',  
	'sis/tpl/general/_footer.html' => 'critic',  
	'sis/tpl/general/_footer_content.html' => 'critic',  
	'sis/tpl/general/_header.html' => 'critic',  
	'sis/tpl/general/_header_platform-menu.html' => 'critic',  
	'sis/tpl/general/_header_title.html' => 'critic',  
	'sis/tpl/general/_header_user-menu.html' => 'critic',  
	'sis/tpl/general/_menu.html' => 'critic',  
	'sis/tpl/general/_popup.html' => 'critic',  
	'sis/tpl/general/_section_top.html' => 'critic',  
			
	//=============================================================== sis > tpl > site >  
	'sis/tpl/site/_menu.html' => 'critic',  
	'sis/tpl/site/forgot-reset-success.html' => 'critic',  
	'sis/tpl/site/forgot-reset.html' => 'critic',  
	'sis/tpl/site/forgot-sent.html' => 'critic',  
	'sis/tpl/site/forgot.html' => 'critic',  
	'sis/tpl/site/home.html' => 'critic',  
	'sis/tpl/site/login.html' => 'critic',  
	'sis/tpl/site/profile-change-password.html' => 'critic',  
	'sis/tpl/site/profile.html' => 'critic',  
	'sis/tpl/site/register.html' => 'critic',  
	'sis/tpl/site/zzz.html' => 'critic',  
			
	//========================================== sis > view >  
	'sis/view/View.php' => 'critic',  
			
	//=============================================================== sis > view > admin >  
	'sis/view/admin/ViewLogin.php' => 'critic',  
	'sis/view/admin/ViewProfile.php' => 'critic',  
	'sis/view/admin/ViewUsers.php' => 'critic',  
	'sis/view/admin/ViewZzz.php' => 'critic',  
			
	//=============================================================== sis > view > crud >  
	'sis/view/crud/ViewZzz.php' => 'critic',  
			
	//=============================================================== sis > view > dev >  
	'sis/view/dev/ViewLog.php' => 'critic',  
	'sis/view/dev/ViewModules.php' => 'critic',  
	'sis/view/dev/ViewRepository.php' => 'critic',  
	'sis/view/dev/ViewSync.php' => 'critic',  
	'sis/view/dev/ViewUsers.php' => 'critic',  
	'sis/view/dev/ViewZzz.php' => 'critic',  
			
	//=============================================================== sis > view > site >  
	'sis/view/site/ViewForgot.php' => 'critic',  
	'sis/view/site/ViewLogin.php' => 'critic',  
	'sis/view/site/ViewProfile.php' => 'critic',  
	'sis/view/site/ViewRegister.php' => 'critic',  
	'sis/view/site/ViewZzz.php' => 'critic',  
			
		
	//=============================================================== cache >  
	'cache' => 'trash'
	];				
	
} 

?>