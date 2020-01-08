<?php

namespace VerifyIdentityNumber;

class VerifyIdentityNumber
{
    /**
     * @param string $idNumber
     *
     * @return array
     */
    public function verifyIdNumberStructure($idNumber)
    {
        $data['result'] = true;
        $data['errors'] = [];

        if (strlen($idNumber) !== 13) {
            $data['errors'][] = 'Invalid length';
        }
        if (!is_numeric($idNumber)) {
            $data['errors'][] = 'Non numeric';
        }

        $year = substr($idNumber, 0, 2);
        $currentYear = date('Y') % 100;
        $prefix = '19';
        if ($year < $currentYear) {
            $prefix = '20';
        }
        $idYear = $prefix . $year;

        $idMonth = substr($idNumber, 2, 2);
        $idDay = substr($idNumber, 4, 2);

        if (!$idYear == substr($idNumber, 0, 2) && $idMonth == substr($idNumber, 2, 2) && $idDay == substr($idNumber, 4, 2)) {
            $data['errors'][] = 'Invalid date';
        } else {
            $data['date_of_birth'] = $idYear . '-' . $idMonth . '-' . $idDay;
        }

        $genderCode = substr($idNumber, 6, 4);
        $data['gender'] = (int)$genderCode < 5000 ? 'Female' : 'Male';

        // 0 for South African citizen, 1 for a permanent resident
        $data['citizenship'] = (int)substr($idNumber, 10, 1) === 0 ? 'citizen' : 'resident';

        /*
         * Calculated as follows using ID Number 800101 5009 087 as an example:
        A. Add all the digits in the odd positions (excluding last digit).
            8 + 0 + 0 + 5 + 0 + 0 = 13...................[1]
        B. Move the even positions into a field and multiply the number by 2.
            011098 x 2 = 22196
        C. Add the digits of the result in b).
            2 + 2 + 1 + 9 + 6 = 20.........................[2]
        D. Add the answer in [2] to the answer in [1].
            13 + 20 = 33
        E. Subtract the second digit (i.e. 3) from 10. The number must tally with the last number in the ID
           Number. If the result is 2 digits, the last digit is used to compare against the last number in the
           ID Number. If the answer differs, the ID number is invalid.
        */
        $total = 0;
        $count = 0;
        for ($i = 0; $i < strlen($idNumber); ++$i) {
            $multiplier = $count % 2 + 1;
            $count++;
            $temp = $multiplier * (int)$idNumber[$i];
            $temp = floor($temp / 10) + ($temp % 10);
            $total += $temp;
        }
        $total = ($total * 9) % 10;

        if ($total % 10 != 0) {
            $data['errors'][] = 'Invalid check digit';
        }

        if (count($data['errors'])) {
            $data['result'] = false;
        }

        return $data;
    }
}