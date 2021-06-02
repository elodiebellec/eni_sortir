<?php
namespace App\Verificators;

class OutingErrors
{
    /*ADD PARTICIPANT*/
    static public string $participantAlreadyRegistered  = "Vous êtes déjà inscrit à cet évènement.";
    static public string $hasReachRegistrationLimit     = "Cet évènement ne comporte plus de places disponibles.";
    static public string $participantToAddIsPlanner     = "Vous organisez cet évènement, de fait vous y participez.";
    static public string $isClosed                      = "Impossible de s'inscrire à un évènement passé ou dont les inscriptions sont cloturées";
    /*REMOVE PARTICIPANT*/
    static public string $participantIsNotRegistered    = "Vous n\'êtes pas inscrit à cet évènement.";
    static public string $participantToRemoveIsPlanner  = "Vous organisez cet évènement, vous ne pouvez pas vous désister.";
    static public string $outingIsNotActive             = "Impossible de se désister d'une activité non active.";
    public static string $outingDoNotExists            = "La sortie n'existe pas";

}