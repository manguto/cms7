
	function combo_ordenar(selector,asc=true,reset=true){
		var select = $(selector);
		select.html(select.find('option').sort(function(x, y) {
		    // to change to descending order switch "<" for ">"
		    if(asc==true){
		    	return $(x).text() > $(y).text() ? 1 : -1;	
		    }else{
		    	return $(x).text() < $(y).text() ? 1 : -1;
		    }			
		}));
		
		//set first option to the empty field (if there is one)
		var selected_key = '';
		select.find('option').each(function(){
			var option = $(this);
			if(option.val()==''){
				select.prepend(option);
			}
			if(option.attr('selected')){
				selected_key = option.val();
			}
		});
		
		//select empty spot
		//log(selected_key);
		
		//marcar selecao previa
		if(selected_key!=''){
			select.val(selected_key);	
		}
		
		//desmarcar selecao previa
		if(reset || selected_key==''){
			select.val('');	
		}
		
	}
	
	/**
	 * PRIVADO - NÃO CHAMAR DIRETAMENTE!
	 * @param combo_selector
	 * @param campos
	 * @returns
	 */
	function PRIVADO___combo_filtrar_acao(combo_selector,campos){
		//log(combo_selector);
		var combo_alvo = $(combo_selector);
		var combo_alvo_options = combo_alvo.find('option');
		
		//de-selecionar campo alvo
		//combo_alvo.val('');
		
		// variavel para registrar os valores atuais nos campos gatilhos para
		// filtragem
		var campos_gatilhos_valores = [];		
		
		// percorre todos os campos gatilhos para obtencao de seus valores e
		// remocao das opcoes do combo alvo
		//log('Percorrimento dos campos gatilho!');
		campos.forEach(function(campo_gatilho_nome, index){			
			//log('CAMPO GATILHO:'+campo_gatilho_nome);
			
			// valor do campo gatilho
			campo_gatilho_valor = $('#'+campo_gatilho_nome).val();
			
			//log('#'+campo_gatilho_nome+'='+campo_gatilho_valor);
			
			// apenas os campos gatilhos definidos terao efeito no combo alvo
			var campo_ativo = campo_gatilho_valor!=undefined && campo_gatilho_valor!=null && campo_gatilho_valor!='';
			//log('STATUS do CAMPO: '+campo_ativo);
			if(campo_ativo){
				//log('campos_gatilhos_valores['+campo_gatilho_nome+']='+campo_gatilho_valor+';');
				campos_gatilhos_valores.push({
					nome : campo_gatilho_nome,
					valor : campo_gatilho_valor
				});
			}
			
		});
		
		//log(campos_gatilhos_valores.length);
		//log(campos_gatilhos_valores);
		
		// percorre as opcoes do campo alvo
		combo_alvo_options.each(function(){
			var option = $(this);
			var option_html = option.html();
			var option_val = option.val();			
			//log('OPTION '+option_html+'['+option_val+']');
			
			if(option_val!=''){	
				//log('OPTION ATIVO');
				
				{// REATIVACAO DE TODOS OS OPTIONS!
					option.prop('disabled',false);
					option.css('color','#000');	
					option.css('display','block');
				}
				
				//verifica os filtros existentes (campos gatilhos)
				campos_gatilhos_valores.forEach(function(campo_gatilho){			
					var campo_gatilho_nome = campo_gatilho.nome;
					var campo_gatilho_valor = campo_gatilho.valor;
					//log('CAMPO GATILHO => nome='+campo_gatilho_nome+' | valor='+campo_gatilho_valor+'');
					
					// ------------------------------
					var option_campo_valor = option.attr(campo_gatilho_nome);			
					//log('SELECT'+combo_selector+' OPTION['+campo_gatilho_nome+'='+option_campo_valor+']');
					
					// apenas os 'options' definidos terao efeito na filtragem					
					if(option_campo_valor!='' && option_campo_valor!=campo_gatilho_valor){
						option.prop('disabled','disabled');
						option.css('color','#f00');
						option.css('display','none');
						//log('OPTION DESATIVADO!');
					}
					
				});/**/								
				
			}/**/
			
		});	/**/
		
	}
	
	/**
	 * FILTRA UM CAMPO COM BASE NOS ATRIBUTOS REFERENCIAIS DO MESMO "xxxxx_id", 
	 * BUSCANDO OS COMBOS REFERENCIADOS ("#xxxx_id") e OCULTADO AS OPCOES
	 * QUE NÃO BATAM COM ESTES INDICES
	 * @param combo_alvo_selector
	 * @param campos
	 * @returns
	 */
	function combo_filtrar(combo_alvo_selector,campos=[]){
		//log('combo_selector);
		var combo_alvo = $(combo_alvo_selector);
		
		{//levantamento dos campos - percorrimento das opcoes do combo alvo para averigracao dos atributos
			combo_alvo.find('option').each(function() {
			  $.each(this.attributes, function() {
			    // this.attributes is not a plain object, but an array
			    // of attribute nodes, which contain both the name and value
			    if(this.specified) {			    	
			    	//parametros
			    	name = this.name;
			    	value = this.value;
			    	{//verificacao de campo identificador relacional
			    		var campo_relacional = name.substr(-3, 3)=='_id';
			    		//log(id);			    		
			    	}
			    	//set?
			    	if(campo_relacional){
			    		campos.push(name);	
			    		//log('Campo relacional! ('+name+')');
			    	}else{
			    		//log('Não um campo relacional ('+name+')');
			    	}			    	
			    }
			  });
			});
			//log(campos);			
		}
		
		campos.forEach(function(campo_gatilho_nome, index){
			//log(index+' => '+campo_gatilho_nome);
			$('#'+campo_gatilho_nome).change(function() {
				PRIVADO___combo_filtrar_acao(combo_alvo_selector,campos);
			});	
			PRIVADO___combo_filtrar_acao(combo_alvo_selector,campos);
		});/**/
		
	}
		
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	