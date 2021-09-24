/**
 * gdrcdSkills
 * E' una classe javascript che permette di gestire l'incremento e il decremento multiplo di più skills in contemporanea
 * 
 * @author Blancks
 */
(function(w) {
    /* Il risultato di questa funzione determina il valore massimo raggiungibile per una skill */
    function maxSkillRank(skillContainer) {
        return 100;
    }

    /* Algoritmo di default per il calcolo del costo del punto successivo */
    function incrementCostAlgo(rank) {
        return 10*rank;
    }
  
    /* Registra le funzioni dei tastini di incremento e decremento per le skills */
    function listenerRegister(skills, incrementButtons, decrementButtons) {
        for (var skill of skills) {
            (function(gdrcdAsyncSkillManagement, skill) {
                var skillid = skill.dataset.skillid;
                
                var incrementButton = document.querySelector(skillSelectorFormat(incrementButtons.trim(), skillid)),
                    decrementButton = document.querySelector(skillSelectorFormat(decrementButtons.trim(), skillid));
                
                // Tasto incremento
                if (incrementButton) {
                    incrementButton.addEventListener('click', function(e) {
                            gdrcdAsyncSkillManagement.skillIncrement(skill);
                            checkForButtonVisibility(
                                gdrcdAsyncSkillManagement,
                                gdrcdAsyncSkillManagement.points, 
                                skills, 
                                incrementButtons, 
                                decrementButtons
                            );
                            clearSelection();
                    });
                }
                
                // Tasto decremento
                if (decrementButton) {
                    decrementButton.addEventListener('click', function(e) {
                            gdrcdAsyncSkillManagement.skillDecrement(skill);
                            checkForButtonVisibility(
                                gdrcdAsyncSkillManagement,
                                gdrcdAsyncSkillManagement.points, 
                                skills, 
                                incrementButtons, 
                                decrementButtons
                            );
                            clearSelection();
                    });
                }
            })(this, skill);
        }
    }
    
    function checkForButtonVisibility(gdrcdAsyncSkillManagement, points, skills, incrementButtons, decrementButtons) {
        for (var skill of skills) {
            (function(gdrcdAsyncSkillManagement, skill) {
                var skillid = skill.dataset.skillid,
                    skillbasevalue = skill.dataset.skillcurvalue,
                    skillcurvalue = parseFloat(skill.innerHTML);
                
                var incrButton = document.querySelector(skillSelectorFormat(incrementButtons.trim(), skillid)),
                    decrButton = document.querySelector(skillSelectorFormat(decrementButtons.trim(), skillid));
                    
                if (!incrButton || !decrButton) {
                    return;
                }
                
                var incrButtonClasses = incrButton.className.split(' '),
                    decrButtonClasses = decrButton.className.split(' ');
                    
                var incrFindInactive = incrButtonClasses.indexOf('inactive'),
                    incrFindActive = incrButtonClasses.indexOf('active'),
                    decrFindInactive = decrButtonClasses.indexOf('inactive'),
                    decrFindActive = decrButtonClasses.indexOf('active');
                
                if (gdrcdAsyncSkillManagement.incrementCost(skillcurvalue+1) > points) {
                    if (incrFindInactive == -1) {
                        if (incrFindActive !== -1) {
                            incrButtonClasses.pop();
                        }
                        
                        incrButtonClasses.push('inactive');
                        incrButton.className = incrButtonClasses.join(' ');
                    }
                } else {
                    if (incrFindActive == -1) {
                        if (incrFindInactive !== -1) {
                            incrButtonClasses.pop();
                        }
                        
                        incrButtonClasses.push('active');
                        incrButton.className = incrButtonClasses.join(' ');
                    }
                }
                
                if ((skillcurvalue <= skillbasevalue && !gdrcdAsyncSkillManagement.allowedToDecrement) || skillcurvalue == 0) {
                    if (decrFindInactive == -1) {
                        if (decrFindActive !== -1) {
                            decrButtonClasses.pop();
                        }
                        
                        decrButtonClasses.push('inactive');
                        decrButton.className = decrButtonClasses.join(' ');
                    }
                } else {
                    if (decrFindActive == -1) {
                        if (decrFindInactive !== -1) {
                            decrButtonClasses.pop();
                        }
                        
                        decrButtonClasses.push('active');
                        decrButton.className = decrButtonClasses.join(' ');
                    }
                }
            })(gdrcdAsyncSkillManagement, skill);
        }
    }
    
    var eventsCallbacks = [];

    /* Classe principale per la gestione degli incrementi/decrementi delle skills */ 
    function gdrcdAsyncSkillManagement(availableContainer, skillsContainer, skillsIncrementButton, skillsDecrementButton, incrementAlgo, maxSkillRankAlgo) {
        this.pointsContainer = document.querySelector(availableContainer);
        this.skillsContainer = document.querySelectorAll(skillsContainer);
        this.points = parseFloat(this.pointsContainer.innerHTML);
        this.allowedToDecrement = false;
      
        if (isNaN(this.points)) {
            cprint("Valore punti disponibili non valido in " + availableContainer);
        }
      
        if (typeof incrementAlgo === 'function') {
            this.incrementCost = incrementAlgo;
        } else {
            this.incrementCost = incrementCostAlgo;
        }
      
        if (typeof maxSkillRankAlgo === 'function') {
            this.maxSkillRank = maxSkillRankAlgo;
        } else {
            this.maxSkillRank = maxSkillRank;
        }
      
        this.register = listenerRegister;
      
        this.allowMasterDecrement = function(allow) {
            this.allowedToDecrement = allow;
            checkForButtonVisibility(this, this.points, this.skillsContainer, skillsIncrementButton, skillsDecrementButton);
        };
        
        this.exportSkillsValue = function() {
            var skills = {};
            
            for (var skill of this.skillsContainer) {
                skills[skill.dataset.skillid] = parseFloat(skill.innerHTML);
            }
            
            return JSON.stringify(skills);
        };
      
        this.pointSub = function(points) {
            if (this.points < points) {
                cprint("Non hai abbastanza punti da spendere");
                return;
            }
          
            this.points -= points;
            this.pointsContainer.innerHTML = this.points;
        };
      
        this.pointAdd = function(points) {
            this.points += points;
            this.pointsContainer.innerHTML = this.points;
        };
      
        this.skillIncrement = function(skillContainer) {
            var skillCurValue = parseFloat(skillContainer.dataset.skillcurvalue);
            var skillNewValue = parseFloat(skillContainer.innerHTML) + 1;
            
            if (this.points >= this.incrementCost(skillNewValue) && skillNewValue <= this.maxSkillRank(skillContainer)) {
                skillContainer.innerHTML = skillNewValue;
                this.pointSub(this.incrementCost(skillNewValue));
                this.onSkillChange();
            }
        };
      
        this.skillDecrement = function(skillContainer) {
            var skillCurValue = parseFloat(skillContainer.dataset.skillcurvalue);
            var skillOldValue = parseFloat(skillContainer.innerHTML);
            var skillNewValue = skillOldValue - 1;

            if (skillNewValue >= skillCurValue || (this.allowedToDecrement && skillNewValue >= 0)) {
                skillContainer.innerHTML = skillNewValue;
                this.pointAdd(this.incrementCost(skillOldValue));
                this.onSkillChange();
            }
        };
        
        this.onSkillChange = function(callback) {
            if (typeof callback == 'function') {
                eventsCallbacks.push(callback);
            }
            
            if (typeof callback === 'undefined') {
                for (var i = 0; i < eventsCallbacks.length; ++i) {
                    eventsCallbacks[i]();
                }
            }
        };
      
        var skillsContainerLength = Object.keys(this.skillsContainer).length,
            skillsIncrButtLenght =  Object.keys(document.querySelectorAll(skillsIncrementButton)).length,
            skillsDecrButtLenght =  Object.keys(document.querySelectorAll(skillsDecrementButton)).length;
          
        if (skillsContainerLength !== skillsIncrButtLenght || skillsContainerLength !== skillsDecrButtLenght) {
            //cprint("Il numero delle skill elencate non è coerente col numero dei tasti di incremento/decremento disponibili. Sicuro di aver selezionato gli elementi giusti ?");
            //Adesso capita anche quando spariscono tutti i tastini in partenza. Basta uscire e fare come se niente fosse
            //return;
        }
      
        this.register(this.skillsContainer, skillsIncrementButton, skillsDecrementButton);
        checkForButtonVisibility(this, this.points, this.skillsContainer, skillsIncrementButton, skillsDecrementButton);
    }
    
    /* Formatta il selettore appropriato per rintracciare l'elemento nel dom associato ad una skill */
    function skillSelectorFormat(baseSelector, skillid) {
        return baseSelector + '[data-skillid="'+ skillid +'"]';
    }
    
    /* Utility per stampare errori custom nella console se presente */
    function cprint(msg) {
        if (typeof console !== 'undefined') {
            console.error(msg);
        }
    }
    
    /* Utility per annullare la selezione del mouse. Utile quando si clicca su degli span anziché dei button */
    function clearSelection() {
        if(document.selection && document.selection.empty) {
            document.selection.empty();
        } else if(window.getSelection) {
            var sel = window.getSelection();
            sel.removeAllRanges();
        }
    }
    
    // Esportiamo all'esterno la classe
    w.gdrcdSkills = gdrcdAsyncSkillManagement;
})(window);