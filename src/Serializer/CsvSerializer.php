<?php


namespace App\Serializer;


use App\Entity\Participant;
use App\Repository\SiteRepository;
use Doctrine\ORM\EntityManagerInterface;

class CsvSerializer
{
    public EntityManagerInterface $entityManager;
    public SiteRepository $siteRepository;

    public function __construct(EntityManagerInterface $entityManager,
                                SiteRepository $siteRepository)
    {
        $this->entityManager = $entityManager;
        $this->siteRepository = $siteRepository;
    }

    /**
     *  Convert CSV to Users
     *  Careful, there is no verification, or no attributes mapping
     *  Attributes must be in this order in the csv :
     * "id","site_id","pseudo","roles","password","last_name","first_name","phone","mail","is_active"
     * @param $filePath
     */
    public function convertToUsers($filePath)
    {
        $row = 1;

        if (($handle = fopen($filePath, "r")) !== FALSE) {

            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $row++;
                $participant = new Participant();
                try {
                    $participant
                        ->setRoles(['ROLE_USER'])
                        ->setSite($this->siteRepository->find($data[1]))
                        ->setPseudo($data[2])
                        ->setPassword($data[4])
                        ->setLastName($data[5])
                        ->setFirstName($data[6])
                        ->setPhone($data[7])
                        ->setMail($data[8])
                        ->setIsActive($data[9]);

                    $this->entityManager->persist($participant);
                }
                /**
                 * In Case site id is not existant or data field is null
                 */
                catch (\Exception $e) {
                    //dd($this->siteRepository->find($data[1]));
                    //fclose($handle);
                    return false;
                }
            }
            //fclose($handle);

            /**
             *In case participant data is not conform to integrity constraints
             */
            try {
                $this->entityManager->flush();
            } catch (\Exception $e) {
                //fclose($handle);
                dd($e);
                return false;
            }

            return true;
        }
    }

    /**
     * TODO: Detect field title on CSV and map it to Participant entity attributes for more flexibility
     * @param $fieldNumber
     * @return string
     */
    private function getAttributes($fieldNumber)
    {
        switch ($fieldNumber) {
            case 1 :
                return 'id';
            case 2 :
                return 'site_id';
            case 3 :
                return 'pseudo';
            case 4 :
                return 'roles';
            case 5 :
                return 'password';
            case 6 :
                return 'last_name';
            case 7 :
                return 'first_name';
            case 8 :
                return 'phone';
            case 9 :
                return 'mail';
            case 10 :
                return 'is_active';
        }
    }
}
