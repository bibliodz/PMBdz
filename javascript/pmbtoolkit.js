var pmbtk={
		c:function(obj,method) {
			return function() {
				if (typeof(method)=="string")
					obj[method].apply(obj,arguments);
				else method.apply(obj,arguments);
			}
		},
		defineClass:function(className,heritedFrom,definition) {
			var classe=function() {
				//Copie de la d�finition
				for (var m in definition) {
					this[m]=definition[m];
				}

				//H�ritage ?
				if (heritedFrom) {
					//Cr�ation de la m�thode parent : elle renvoie une m�thode du parent dans le contexte de l'instance et non du prototype
					this.parent=function(parentMethod) {
						return pmbtk.c(this,window[className].prototype[parentMethod]);
					}
				}
				
				this.construct.apply(this,arguments);
			}
			//Cr�ation de la fonction dans l'espace global
			window[className]=classe;
			
			//H�ritage ? Si oui, on charge le prototype !
			if (heritedFrom) {
				parentClass=window[heritedFrom];
				//Ajout des m�thodes du parent au prototype
				window[className].prototype=new parentClass();
				//Ajout des m�thodes du prototype du parent au prototype
				for (var p in parentClass.prototype){
					if((typeof(parentClass.prototype[p]=="function"))&&(!window[className].prototype[p])) {
						window[className].prototype[p]=parentClass.prototype[p];
					}	
				}
			}
		}	
}


