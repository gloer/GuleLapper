<!DOCTYPE html>
<html><head>
<title>Den elektroniske korktavle</title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<meta name="description" content="" />
<script type="text/javascript" src="js/jquery-1.8.3.min.js"></script>							<!-- jQuery lokalt -->
<script type="text/javascript" src="js/kickstart.js"></script>                                  <!-- KICKSTART -->
<script type="text/javascript" src="js/knockout-2.2.1.js"></script>								<!-- Knockout.js -->
<link rel="stylesheet" type="text/css" href="css/kickstart.css" media="all" />                  <!-- KICKSTART -->
<link rel="stylesheet" type="text/css" href="style.css" media="all" />                          <!-- CUSTOM STYLES -->
<style>
	/* Sett bredde på grid */
	.grid { width:1024px; }
	.lapp {
		border:1px solid #CCC;
		border-radius:5px;	
		background:#FFF;		
	}
	.mini {
		float:left;
		width:15px;
		height:15px;
		border:1px solid grey;
		margin-left:2px;
		margin-right:2px;
		margin-bottom:2px;
		cursor:pointer;
	}

</style>
</head><body>

<div class="grid">
<!-- ===================================== END HEADER ===================================== -->


	<div class="col_12" style="margin-top:20px;">
		<div class="col_2">
        	<button class="blue" data-bind="click:nyLapp">Lag ny lapp <i class="icon-plus"></i></button>
        </div>
        <div class="col_2">
        	
            <div data-bind="foreach:farger" class="clearfix">
            	<div class="mini" data-bind="style: {background:$data, borderWidth: $data == $parent.valgtFarge() ? '2px' : '1px'}, event: {click:$root.filterFarge}"></div>
            </div>
            <div>Filtrer på farge</div>
        </div>
        <div class="col_3">
        	<input data-bind="value:sokeord, event: {keyup:sok}, valueUpdate: 'afterkeydown'" type="text" placeholder="Søk i notater">
        </div>
        
	</div>
    <div class="col_12">
    
     <!-- ko foreach:lappene -->
               
            <div class="col_3 lapp" data-bind="style: {background:valgtFarge}, visible:synlig">
            	
            	<div class="full-width">
                	<ul class="menu">
                        <li><a href="#" data-bind="click:settRedigerbar, visible:!blirRedigert()">Rediger <i class="icon-edit"></i></a></li>
                        <li><a href="#" data-bind="click:lagre, visible:blirRedigert()">Lagre <i class="icon-save"></i></a></li>
                        <li><a href="#" data-bind="click:$root.slett">Slett <i class="icon-trash"></i></a></li>                      
                    </ul>
                </div>           
                <div data-bind="text:tekst, visible:!blirRedigert()" style="padding:10px; height:90px; overflow:auto;"></div>
                <textarea data-bind="value:tekst, visible:blirRedigert" style="width:100%; height:90px;">
                	
                </textarea>
                <div style="padding:10px;">
                	<!-- ko foreach:farger -->
                	<div class="mini" data-bind="style: {background:$data}, event: {click:$parent.settFarge}">
                    	
                    </div>
                    <!-- /ko -->
                     <div style="float:right">
                		<span data-bind="text:standardDato">12.04.13</span> <i class="icon-calendar"></i>
                	</div>
                </div>
               
                                    
            </div> 
                    
        
       
       
       
        <!-- /ko -->     
    
    
    </div>
    
    

<!-- ===================================== START FOOTER ===================================== -->
</div><!-- END GRID-->


   <script>
		var konstanter = {
			farger: ["#ACE8A4", "#D9FFB4", "#CBFF7C", "#B4E831", "#FFFA47"]			
		}
	
		var Main = function () {
			var self = this;

			self.lappene = ko.observableArray([]);
			
			self.nyLapp = function() {
				var lapp = new Lapp("Overskrift", "", "gul");
				lapp.blirRedigert(true);
				
				self.lappene.push(lapp);
				self.oppdaterJSON();	
			};					
			
			self.oppdaterJSON = function() {
				var data = ko.toJSON(self.lappene());
				//alert(data);
				
				$.ajax({
					type: "POST",
					url: "jsonSTORE.php",
					data: {"data" : data},
					success: function(){
						console.log("SUPERT");	
					},
					dataType: JSON
				});
														
			};
			
			self.slett = function() {				
				self.lappene.remove(this);	
				self.oppdaterJSON();
			};
			
			self.sokeord = ko.observable("");
			
			self.sok = function() {
				//Passer på at vi søker i alle boksene.
				//Kan filtrere på farge i etterkant hvis ønskelig
				main.valgtFarge("alleFarger");
				var ord = self.sokeord();
				for (var i=0; i<self.lappene().length; i++) {
					var str = self.lappene()[i].tekst();
					if (str.search(ord,"i") >= 0) {
						self.lappene()[i].vis();							
					} else {
						self.lappene()[i].skjul();	
					}
					
					console.log(str.search(ord,"gi"));
					
					console.log(str);					
				}
				
			};
			
			self.farger = konstanter.farger;
			//Ingen valgte farger i starten. Viser kun lapper med valgt farge.
			//Hvis ingen farge er valgt, viser vi alle farger.
			self.valgtFarge = ko.observable("alleFarger");		
			
			self.filterFarge = function (index) {								
				if (index == self.valgtFarge()) {
					self.valgtFarge("alleFarger");
				} else {
					self.valgtFarge(index);	
				}
			}
			
			
			//Leser inn data fra den samme filen som vi lagrer til når vi 
			//lagrer et notat
			self.lastInnData = function()
			{
			var xmlhttp;
			if (window.XMLHttpRequest)
			  {// code for IE7+, Firefox, Chrome, Opera, Safari
			  xmlhttp=new XMLHttpRequest();
			  }
			else
			  {// code for IE6, IE5
			  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
			  }
			xmlhttp.onreadystatechange=function()
			  {
			  if (xmlhttp.readyState==4 && xmlhttp.status==200)
				{
					var jsonFraServer = xmlhttp.responseText;
					var json = jsonFraServer.replace(/[/\\*]/g, "");
					json = JSON.parse(json);
					//alert(json.length);
					
					for (var i=0; i<json.length; i++) {
						var lapp = new Lapp ("", json[i].tekst, json[i].valgtFarge);
						lapp.settFarge(json[i].valgtFarge);
						self.lappene.push(lapp); 	
					}
					
					
					
				}
			  }
			 xmlhttp.open("GET","array1.json",true);
			 xmlhttp.send();
			}
				
			
			self.lastInnData();	
			
		
			
		};		
		
	
		var Lapp = function (overskrift, tekst, farge) {
			var self = this;
			self.skalVises = ko.observable(true);
			self.fargeKlikk = ko.observable(0);
			self.blirRedigert = ko.observable(false);
			self.overskrift = ko.observable(overskrift);
			self.tekst = ko.observable(tekst);
			self.farger = ko.observableArray(konstanter.farger);
			self.valgtFarge = ko.observable(self.farger()[0]);
			self.settFarge = function(index) {				
				self.valgtFarge(index);	
			};
			self.settRedigerbar = function() {
				self.blirRedigert(true);	
			};
			self.lagre = function() {
				self.blirRedigert(false);
				main.oppdaterJSON();
			}
			self.standardDato = ko.computed( function( ){
				var dato = new Date();
				//Setter den synlig fra dagens dato
				//dato.setDate(dato.getDate() + 7);
				var dag = dato.getDate();
				var mnd = dato.getMonth() + 1;
				var aar = dato.getFullYear();
				
				//Legger på en null hvis dag og mnd er mindre enn 10, så det står 10.02 i stedet for 10.2
				dag = dag > 9 ? dag : "0" + dag;
				mnd = mnd > 9 ? mnd : "0" + mnd;
				
				return dag + "." + mnd + "." + aar;
			});
			
			self.vis = function() {
				self.skalVises(true);
			}
			self.skjul = function() {
				self.skalVises(false);	
			}
			
			self.synlig = ko.computed( function() {
				//return !self.fargefiltrertBort();
				//alert(main.valgtFarge());
				return self.skalVises() && ( main.valgtFarge() == "alleFarger" || self.valgtFarge() == main.valgtFarge() );
			});
			
			
			
			
		};
	
		
		
		var main = new Main();
		ko.applyBindings(main);
	
	</script>


</body></html>