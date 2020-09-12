#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Created on Sat Sep 12 09:23:17 2020

@author: Sylvain
"""


import numpy as np
import os


###############################################################################
# This code extract individual logs
###############################################################################



#Unfortunatly those vectors needs to be re-done for ES and EN
################################ FR ###########################################
char_list=['Terrence', 'Gioele', 'Chun', 'Andie', 'Finola','Ian', 'Frieda', 'Stephen', 'Eleesha', 'Roland', 'Jin Su','Derek','Chao', 'Hua', 'Kuan Ti','Paola']# removed 'Raluca' and 'Janice'
equ_list=["Antenne","Terminal Astro","Lit","Terminal BIOS","Calculateur","Machine à café","Chambre de Combustion","Terminal de Commandement","Centre de Communication","Module Cryo®","Porte","Dynarcade","Réacteur d'urgence","Réservoir de Fuel","Simulateur de Gravité","Cuisine","Réacteur Latéral","Mycoscan","Distilateur de Stupéfiants","Distilleur de Stupéfiant","Coeur de NERON","Réservoir d'Oxygène","Pasiphae","Patrouilleur","Pilgred","Scanner de Planète","Laboratoire de Recherche","Douche","Sofa suédois","Plot Chirurgical","Douche","Poste de Pilotage","Poste de tir","Accès : Icarus","Jukebox"]
item_list=["Super Savon","Fusil Natamy","Thermos de Café","Bloc de Pense-Bête","Drone de Soutien","Plan du Module Babel","Plan de l'Écholocateur","Plan de l'Extincteur","Plan de la Grenade","Livre","Plan","Plan du Lizaro Jungle","Plan de la Sulfateuse","Plan du Vaguoscope","Plan du Lance-Roquette","Plan du Casque de Visée","Plan du Drone de Soutien","Plan Du Sofa Suédois","Plan Du ThermoSenseur","Plan Du Drapeau Blanc","Apprentron : Astrophysicien","Apprentron : Biologiste","Apprentron : Botaniste","Apprentron : Diplomate","Apprentron : Pompier","Apprentron : Cuistot","Apprentron : Informaticien","Apprentron : Logistique","Apprentron : Médecin","Apprentron : Pilote","Apprentron : Expert Radio","Apprentron : Robotiques","Apprentron : Tireur","Apprentron : Psy","Apprentron : Sprinter","Apprentron : Technicien","Vieux T-Shirt","Débris Plastique","Débris Métallique","Tube Epais","Manuel Du Commandant","Document","De La Recherche Sur Le Mush","Pense-Bête","bacta","betapropyl","eufurysant","nuke","rixolam","ponay","pimp","rosebud","soma","épice","twïnoid","xenox","Module Babel","Foreuse","EchoLocateur","Boussole quadrimetric","Corde","ThermoSenseur","Drapeau Blanc","Anémole","Banane","Bottine","Calebotte","Lianube","Filandru","Fragilane","Goustiflon","Citrouïd","Kubinus","Balargine","pénicule","Pénicule","Penicule","Toupimino","Precati","Clé à Molette","Décapsuleur Alien","Trottinette Anti-Grav.","iTrakie®©","Lentille NCC","Vaguoscope","Armure de Plastenite","Gants de protection","Monture Rocheuse","Casque de Visée","Savon","Combinaison","Tablier intachable","Traqueur","Talkie-Walkie","Bandage","Lubrifiant Alien","Médikit","Sérum Retro-Fongique","Suceur de Spore","Caméra","Caméra Installée","Cartouche Invertébré","Carte Liquide de Magellan","Disquette du Gênome Mush","Souche de test Mush","Myco-Alarme","Gelée à Circuits Imprimés","Morceau de carte stellaire","Bâtonnet Aqueux","Schrödinger","Drone De Soutien","Capsule de Fuel","HydroPot","Panier Repas","Capsule d'Oxygène","Capsule Spatiale","Kit De survie","Sofa Suédois","Sofa Suédois","Asperginulk","Bananier","Bifalon","Cucurbitatrouille","Buitalien","Cactuor","Lianiste","Fiboniccus","Peuplimoune","Mycopia","Platacia","Precatus","Poulmino","Tubiliscus","Jeune Asperginulk","Jeune Bananier","Jeune Bifalon","Jeune Cucurbitatrouille","Jeune Buitalien","Jeune Cactuor","Jeune Lianiste","Jeune Fiboniccus","Jeune Peuplimoune","Jeune Mycopia","Jeune Platacia","Jeune Precatus","Jeune Poulmino","Jeune Tubiliscus","Steak alien","Anabolisant","Café","Ration cuisinée","Barre de Lombrics","Déchets Organiques","Riz soufflé proactif","Patate spatiale","Ration standard","Barre Supravitaminée","Télé Holographique alien","Ruban Adhésif","Extincteur","Bidouilleur","MAD Kube","Micro-onde","Supergélateur","Tabulatrice","Blaster","Grenade","Couteau","Lizaro Jungle","Natamy", "Sulfateuse","Lance-Roquette"]
skills_list=["Anonyme","Parfum Antique","Apprentissage","Astrophysicien","Bacterophilie","Biologiste","Botaniste","Conspirateur","Caféinomane","Cuistot","Sang-froid","Confident","Oeil fou","Créatif","Dialoguiste","Contact Déprimant","Concepteur","Détaché","Persévérant","Dévotion","Diplomatie","Portier","Expert","Fertile","Pompier","Frugivore","Cuisine Fongique","Génie","Gelée Verte","Main Verte","Canonnier","Dur à Cuire","Hygiéniste","Infecteur","Intimidant","Informaticien","Leader","Lethargie","Logistique","Seul espoir de l'Humanité","Moisification de Masse","Médecin","Métalo","Motivateur","Esprit du Mycéllium","Mycologiste","Dépression de NERON","Seule amie de NERON","Cauchemardesque","Doigt De Fée","Ninja","Infirmier","Observateur","Méticuleuse","Arriviste","Panique","Paranoïaque","Phagocytose","Physicien","Pilote","Politicien","Polymathe","Polyvalent","Pressentiment","Expert radio","Piratage radio","Rebelle","Robotique","Saboteur","Abnégation","Tireur","Psy","Piège Moisi","Fuyant","Robuste","Résistance à l'Eau","Sprinter","Stratéguerre","Survie","Technicien","Optimiste","Bourreau","Traqueur","Traître","Transfert","Piégeur","Retour Arrière","Persécuteur","Lutteur"]
hunter_list=["Hunter", "Trax","Transport","Arack","Astéroïde","D1000"]
injuries_list=['Absence de bras', 'Articulation du bras fort morte','Balle en ballade', 'Bras brulés','Brulure au 3ème degré sur 90%','Brulûre au 3ème degré sur 50%', "Cerveau à l'air libre",'Doigt cassé', 'Doigt manquant', 'Epaule brisée','Epaule froissée', 'Epaule pulvérisée', "Foie hors d'état",'Hemorragie critique', 'Hémorragie', 'Jambe cassée.','Jambes inutilisables', 'Langue cisailée', 'Main brûlée','Main en charpie', 'Nez explosé', 'Oreille incapacités','Oreille interne déréglé.', 'Oreille pulvérisée','Pied cassé.', 'Pied en bouillie.', 'Poumon à trou','Trauma crânien', 'côtes pétée']
disorders_list=['Vertige chronique.','Dépression','Migraine chronique','Agoraphobie','Crabisme','Crise Paranoïaque’,’Episodes psychotiques','Phobie des armes','Dépression','Coprolalie','Episodes psychotiques','Phobie des armes','Ailurophobie','Agoraphobie','Spleen','Vertige chronique.','Crabisme','Crise $skill','Migraine chronique','Spleen','Coprolalie','Ailurophobie']
deseases_list=['Migraine','GastroEntérite','Verdoiement','Morsure Noire','Carence en vitamines','Variole','Eruption cutanée','Reflux Gastriques','Intoxication Alimentaire','Nausée légère','Citrouillite','Grippe','Rhume','Vers Solitaire','Acouphènes Extrême','Rage Spatiale','Infection aïgue','Infection fongique','Tempête sinusale','Rubeole','Syphilis','Allergie au chat','Allergie au mush','Oedeme de Quincke']
titles_list=["Commandant", "Admin. NERON","Resp. Communications"]
project_list=['Hydropots supplémentaires','Bouclier plasma',"Détecteurs d'incendie",'Démantèlement','Rafistolage Général','Coursives blindées','Propulseurs antigrav','Acceleration du processeur','Canon blaster','Isolateur Phonique','Réducteur de trainée','Conduite Oxygénées','Lampes a chaleur','Propulseur de décollage','Drone supplémentaire','Visée Heuristique','Distributeur pneumatique','Lavabo opportun','Détecteurs de pistons défectueux','Thalasso','Protocole ACTOPI','Tas de débris','Filet magnetique','Détecteur à ondes de probabilité','Toréfacteur a fission','Reservoir de Teslatron','Arroseurs automatiques','Agrandissement de la cale','Terminaux auxiliaires','Portail de décollage extra-large','Couveuse hydroponique','Pulsateur inversé','Chauffage au sol','Nano Coccinnelles','Rapatriement magnetique','Radar à ondes spatiales',"Détecteur d'anomalie",'Participation de NERON','Cuisine SNC','Jukebox']
list_of_death=['Assassiné','Daedalus détruit','Plaque de métal','Famine','Dépression fatale','Décapité','Blessures...','Saigné','Abandonné','Aventurier perdu','Combat spatial','Brûlé','Mis en quarantaine par NERON','Aventurier pas assez combatif','Circonstances funestes','Assassinés par NERON','Septicémie','Electrocuté','Aventurier malchanceux','Allergie','Aventurier Trop curieux']
####################################é###########################################


all_lists=[char_list,item_list,equ_list,skills_list,hunter_list,
           injuries_list,disorders_list,deseases_list, titles_list,
           project_list,
           list_of_death]
replace_lists=['$char','$item','$equipment','$skill','$hunter',
               '$injurie','$disorder','$desease',"$title",
               "$project",
               "$cause_of_death"]


folder='/Users/Sylvain/Desktop/Personnel/logs_Mush2/'



ev_name  =[]



###############################################################################
# We are making this little counter to know the number of time repairing hurt someone
###############################################################################
## 'EV:REPAIR_HURT' despite its name it is not only repairing but also other such as pick
## 'EV:OBJECT_NOT_REPAIRED'

for i_ship in os.listdir(folder):
    if os.path.isdir(folder + i_ship):

        sub_folder=folder+i_ship+'/'
        for i_char_file in os.listdir(sub_folder):
            if 'NERON.txt' in i_char_file:
                file=sub_folder+i_char_file
            
                log_file=open(file, 'r')
                data=log_file.readlines()
                

                for i in range(0, len(data)):
                    log_i=data[i]
                    
                    if ' | ' in log_i:
                        start= log_i.find(' | ')+3
                        end  = -1
                        
                        ev_name_i=log_i[start:end]
                        
                     
                    
                        #now let's remoove the names
                        for i_list in [0,1,2,8,9,10]:
                            char_list=all_lists[i_list]
                            new_string=replace_lists[i_list]
                            
                            for i_char in char_list:
                                while(i_char+'*' in ev_name_i or
                                      i_char+' ' in ev_name_i or
                                      i_char+')' in ev_name_i or
                                      i_char+',' in ev_name_i or
                                      i_char+'.' in ev_name_i):  
                                    if (i_char+'*' in ev_name_i or
                                        i_char+' ' in ev_name_i or
                                        i_char+')' in ev_name_i or
                                        i_char+',' in ev_name_i or
                                        i_char+'.' in ev_name_i):
                                        start_replace=ev_name_i.find(i_char)
                                        end_replace  = start_replace + len(i_char)
                                        ev_name_i = ev_name_i[:start_replace]+new_string+ev_name_i[end_replace:]
                                        
                                        
                                        
                        
                        
                        if not(ev_name_i in ev_name):
                            ev_name.append(str(ev_name_i))
                            
                log_file.close()    



for i in ev_name:
    if ("||réattribuer le titre||" in i or
        "||titre réattribué||" in i    or
        "||Accès lointains ouverts.||" in i or
        "ncendie*" in i or
        "équipement hors d'usage" in i or
        "suppression d'un équipement vital" in i) and (
        'Raluca' in i or 'Janice' in i):
                
                start=i.find('Raluca')
                i= i[:start]+ "$char"+ i[start+6:]
                
                
                start=i.find('Janice')
                i= i[:start]+ "$char"+ i[start+6:]
                
                



ev_name=np.array(np.unique(ev_name))


###### save a file
save_file= '/Users/sylvain/eMush_project/Gathering_mush_data/NERON_mesages.txt'

saving_file = open(save_file, 'w')
for i in ev_name:
    saving_file.write(i + '\n')
    
    

    
saving_file.close()
    

