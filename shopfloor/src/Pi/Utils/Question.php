<?php

declare(strict_types=1);

namespace App\Pi\Utils;

class Question
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var int
     */
    protected $parentId;

    /**
     * @var int
     */
    protected $tOpno;

    /**
     * @var int
     */
    protected $tItem;

    /**
     * @var string
     */
    protected $model;

    /**
     * @var string
     */
    protected $owner;

    /**
     * @var int
     */
    protected $mtl;

    /**
     * @var int
     */
    protected $sha;

    /**
     * @var int
     */
    protected $she;

    /**
     * @var int
     */
    protected $sor;

    /**
     * @var int
     */
    protected $stl;

    /**
     * @var int
     */
    protected $win;

    /**
     * @var int
     */
    protected $wim;

    /**
     * @var int
     */
    protected $leb;

    /**
     * @var int
     */
    protected $aer;

    /**
     * @var int
     */
    protected $pow;

    /**
     * @var int
     */
    protected $wux;

    /**
     * @var int
     */
    protected $mai;

    /**
     * @var int
     */
    protected $wol;

    /**
     * @var string
     */
    protected $subjectEn;

    /**
     * @var string
     */
    protected $subjectFr;

    /**
     * @var string
     */
    protected $subjectZh;

    /**
     * @var string
     */
    protected $descEn;

    /**
     * @var string
     */
    protected $descFr;

    /**
     * @var string
     */
    protected $descZh;

    /**
     * @var int
     */
    protected $position;

    /**
     * @var string
     */
    protected $helpEn;

    /**
     * @var string
     */
    protected $helpFr;

    /**
     * @var string
     */
    protected $helpZh;

    /**
     * @var int
     */
    protected $attachmentEn;

    /**
     * @var int
     */
    protected $attachmentFr;

    /**
     * @var int
     */
    protected $attachmentZh;

    /**
     * @var string
     */
    protected $answerType;

    /**
     * @var string
     */
    protected $answerUnit;

    /**
     * @var string
     */
    protected $componentSn;

    /**
     * @var int
     */
    protected $matchList;

    /**
     * @var string
     */
    protected $answerMax;

    /**
     * @var string
     */
    protected $answerMin;

    /**
     * @var string
     */
    protected $nonConformity;

    /**
     * @var string
     */
    protected $createdOn;

    /**
     * @var int
     */
    protected $enteredBy;

    /**
     * @var string
     */
    protected $updatedOn;

    /**
     * @var int
     */
    protected $updatedBy;

    /**
     * @var \DateTime
     */
    protected $dtValidity;

    /**
     * @var \DateTime
     */
    protected $dtExpiration;

    /**
     * @var string
     */
    protected $createMode;

    /**
     * @var string
     */
    protected $gt1;

    /**
     * @var string
     */
    protected $gt3;

    /**
     * @var string
     */
    protected $active;

    /**
     * Question constructor.
     *
     * @param array $question
     */
    public function __construct($question = [])
    {
        if (!empty($question)) {
            foreach ($question as $indent => $value) {
                switch ($indent) {
                    case 'id':
                        $this->id = $value;
                        break;
                    case 'parent_id':
                        $this->setParentId($value);
                        break;
                    case 't_opno':
                        $this->setTOpno($value);
                        break;
                    case 't_item':
                        $this->setTItem($value);
                        break;
                    case 'model':
                        $this->setModel($value);
                        break;
                    case 'owner':
                        $this->setOwner($value);
                        break;
                    case 'mtl':
                        $this->setMtl($value);
                        break;
                    case 'sha':
                        $this->setSha($value);
                        break;
                    case 'she':
                        $this->setShe($value);
                        break;
                    case 'sor':
                        $this->setSor($value);
                        break;
                    case 'stl':
                        $this->setStl($value);
                        break;
                    case 'win':
                        $this->setWin($value);
                        break;
                    case 'wim':
                        $this->setWim($value);
                        break;
                    case 'leb':
                        $this->setLeb($value);
                        break;
                    case 'aer':
                        $this->setAer($value);
                        break;
                    case 'pow':
                        $this->setPow($value);
                        break;
                    case 'wux':
                        $this->setWux($value);
                        break;
                    case 'mai':
                        $this->setMai($value);
                        break;
                    case 'wol':
                        $this->setWol($value);
                        break;
                    case 'subject_en':
                        $this->setSubjectEn(addslashes($value));
                        break;
                    case 'subject_fr':
                        $this->setSubjectFr(addslashes($value));
                        break;
                    case 'subject_zh':
                        $this->setSubjectZh(addslashes($value));
                        break;
                    case 'desc_en':
                        $this->setDescEn(addslashes($value));
                        break;
                    case 'desc_fr':
                        $this->setDescFr(addslashes($value));
                        break;
                    case 'desc_zh':
                        $this->setDescZh(addslashes($value));
                        break;
                    case 'position':
                        $this->setPosition($value);
                        break;
                    case 'help_en':
                        $this->setHelpEn($value);
                        break;
                    case 'help_fr':
                        $this->setHelpFr($value);
                        break;
                    case 'help_zh':
                        $this->setHelpZh($value);
                        break;
                    case 'attachment_en':
                        $this->setAttachmentEn($value);
                        break;
                    case 'attachment_fr':
                        $this->setAttachmentFr($value);
                        break;
                    case 'attachment_zh':
                        $this->setAttachmentZh($value);
                        break;
                    case 'answer_type':
                        $this->setAnswerType($value);
                        break;
                    case 'answer_unit':
                        $this->setAnswerUnit($value);
                        break;
                    case 'component_sn':
                        $this->setComponentSn($value);
                        break;
                    case 'match_list':
                        $this->setMatchList($value);
                        break;
                    case 'answer_max':
                        $this->setAnswerMax($value);
                        break;
                    case 'answer_min':
                        $this->setAnswerMin($value);
                        break;
                    case 'non_conformity':
                        $this->setNonConformity($value);
                        break;
                    case 'created_on':
                        $this->setCreatedOn($value);
                        break;
                    case 'entered_by':
                        $this->setEnteredBy($value);
                        break;
                    case 'updated_on':
                        $this->setUpdatedOn($value);
                        break;
                    case 'updated_by':
                        $this->setUpdatedBy($value);
                        break;
                    case 'dt_validity':
                        $this->setDtValidity($value);
                        break;
                    case 'dt_expiration':
                        $this->setDtExpiration($value);
                        break;
                    case 'create_mode':
                        $this->setCreateMode($value);
                        break;
                    case 'gt1':
                        $this->setGt1($value);
                        break;
                    case 'gt3':
                        $this->setGt3($value);
                        break;
                    case 'active':
                        $this->setActive($value);
                        break;
                }
            }
        }
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * @param int $parentId
     */
    public function setParentId($parentId)
    {
        $this->parentId = $parentId;
    }

    /**
     * @return int
     */
    public function getTOpno()
    {
        return $this->tOpno;
    }

    /**
     * @param int $tOpno
     */
    public function setTOpno($tOpno)
    {
        $this->tOpno = $tOpno;
    }

    /**
     * @return int
     */
    public function getTItem()
    {
        return $this->tItem;
    }

    /**
     * @param int $tItem
     */
    public function setTItem($tItem)
    {
        $this->tItem = $tItem;
    }

    /**
     * @return string
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param string $model
     */
    public function setModel($model)
    {
        $this->model = $model;
    }

    /**
     * @return string
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param string $owner
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;
    }

    /**
     * @return int
     */
    public function getMtl()
    {
        return $this->mtl;
    }

    /**
     * @param int $mtl
     */
    public function setMtl($mtl)
    {
        $this->mtl = $mtl;
    }

    /**
     * @return int
     */
    public function getSha()
    {
        return $this->sha;
    }

    /**
     * @param int $sha
     */
    public function setSha($sha)
    {
        $this->sha = $sha;
    }

    /**
     * @return int
     */
    public function getShe()
    {
        return $this->she;
    }

    /**
     * @param int $she
     */
    public function setShe($she)
    {
        $this->she = $she;
    }

    /**
     * @return int
     */
    public function getSor()
    {
        return $this->sor;
    }

    /**
     * @param int $sor
     */
    public function setSor($sor)
    {
        $this->sor = $sor;
    }

    /**
     * @return int
     */
    public function getStl()
    {
        return $this->stl;
    }

    /**
     * @param int $stl
     */
    public function setStl($stl)
    {
        $this->stl = $stl;
    }

    /**
     * @return int
     */
    public function getWin()
    {
        return $this->win;
    }

    /**
     * @param int $win
     */
    public function setWin($win)
    {
        $this->win = $win;
    }

    /**
     * @return int
     */
    public function getWux()
    {
        return $this->wux;
    }

    /**
     * @param int $wux
     */
    public function setWux($wux)
    {
        $this->wux = $wux;
    }

    /**
     * @return string
     */
    public function getSubjectEn()
    {
        return $this->subjectEn;
    }

    /**
     * @param string $subjectEn
     */
    public function setSubjectEn($subjectEn)
    {
        $this->subjectEn = $subjectEn;
    }

    /**
     * @return string
     */
    public function getSubjectFr()
    {
        return $this->subjectFr;
    }

    /**
     * @param string $subjectFr
     */
    public function setSubjectFr($subjectFr)
    {
        $this->subjectFr = $subjectFr;
    }

    /**
     * @return string
     */
    public function getSubjectZh()
    {
        return $this->subjectZh;
    }

    /**
     * @param string $subjectZh
     */
    public function setSubjectZh($subjectZh)
    {
        $this->subjectZh = $subjectZh;
    }

    /**
     * @return string
     */
    public function getDescEn()
    {
        return $this->descEn;
    }

    /**
     * @param string $descEn
     */
    public function setDescEn($descEn)
    {
        $this->descEn = $descEn;
    }

    /**
     * @return string
     */
    public function getDescFr()
    {
        return $this->descFr;
    }

    /**
     * @param string $descFr
     */
    public function setDescFr($descFr)
    {
        $this->descFr = $descFr;
    }

    /**
     * @return string
     */
    public function getDescZh()
    {
        return $this->descZh;
    }

    /**
     * @param string $descZh
     */
    public function setDescZh($descZh)
    {
        $this->descZh = $descZh;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param int $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @return string
     */
    public function getHelpEn()
    {
        return $this->helpEn;
    }

    /**
     * @param string $helpEn
     */
    public function setHelpEn($helpEn)
    {
        $this->helpEn = $helpEn;
    }

    /**
     * @return string
     */
    public function getHelpFr()
    {
        return $this->helpFr;
    }

    /**
     * @param string $helpFr
     */
    public function setHelpFr($helpFr)
    {
        $this->helpFr = $helpFr;
    }

    /**
     * @return string
     */
    public function getHelpZh()
    {
        return $this->helpZh;
    }

    /**
     * @param string $helpZh
     */
    public function setHelpZh($helpZh)
    {
        $this->helpZh = $helpZh;
    }

    /**
     * @return int
     */
    public function getAttachmentEn()
    {
        return $this->attachmentEn;
    }

    /**
     * @param int $attachmentEn
     */
    public function setAttachmentEn($attachmentEn)
    {
        $this->attachmentEn = $attachmentEn;
    }

    /**
     * @return int
     */
    public function getAttachmentFr()
    {
        return $this->attachmentFr;
    }

    /**
     * @param int $attachmentFr
     */
    public function setAttachmentFr($attachmentFr)
    {
        $this->attachmentFr = $attachmentFr;
    }

    /**
     * @return int
     */
    public function getAttachmentZh()
    {
        return $this->attachmentZh;
    }

    /**
     * @param int $attachmentZh
     */
    public function setAttachmentZh($attachmentZh)
    {
        $this->attachmentZh = $attachmentZh;
    }

    /**
     * @return string
     */
    public function getAnswerType()
    {
        return $this->answerType;
    }

    /**
     * @param string $answerType
     */
    public function setAnswerType($answerType)
    {
        $this->answerType = $answerType;
    }

    /**
     * @return string
     */
    public function getAnswerUnit()
    {
        return $this->answerUnit;
    }

    /**
     * @param string $answerUnit
     */
    public function setAnswerUnit($answerUnit)
    {
        $this->answerUnit = $answerUnit;
    }

    /**
     * @return string
     */
    public function getComponentSn()
    {
        return $this->componentSn;
    }

    /**
     * @param string $componentSn
     */
    public function setComponentSn($componentSn)
    {
        $this->componentSn = $componentSn;
    }

    /**
     * @return int
     */
    public function getMatchList()
    {
        return $this->matchList;
    }

    /**
     * @param int $matchList
     */
    public function setMatchList($matchList)
    {
        $this->matchList = $matchList;
    }

    /**
     * @return string
     */
    public function getAnswerMax()
    {
        return $this->answerMax;
    }

    /**
     * @param string $answerMax
     */
    public function setAnswerMax($answerMax)
    {
        $this->answerMax = $answerMax;
    }

    /**
     * @return string
     */
    public function getAnswerMin()
    {
        return $this->answerMin;
    }

    /**
     * @param string $answerMin
     */
    public function setAnswerMin($answerMin)
    {
        $this->answerMin = $answerMin;
    }

    /**
     * @return string
     */
    public function getNonConformity()
    {
        return $this->nonConformity;
    }

    /**
     * @param string $nonConformity
     */
    public function setNonConformity($nonConformity)
    {
        $this->nonConformity = $nonConformity;
    }

    /**
     * @return string
     */
    public function getCreatedOn()
    {
        return $this->createdOn;
    }

    /**
     * @param string $createdOn
     */
    public function setCreatedOn($createdOn)
    {
        $this->createdOn = $createdOn;
    }

    /**
     * @return int
     */
    public function getEnteredBy()
    {
        return $this->enteredBy;
    }

    /**
     * @param int $enteredBy
     */
    public function setEnteredBy($enteredBy)
    {
        $this->enteredBy = $enteredBy;
    }

    /**
     * @return string
     */
    public function getUpdatedOn()
    {
        return $this->updatedOn;
    }

    /**
     * @param string $updatedOn
     */
    public function setUpdatedOn($updatedOn)
    {
        $this->updatedOn = $updatedOn;
    }

    /**
     * @return int
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }

    /**
     * @param int $updatedBy
     */
    public function setUpdatedBy($updatedBy)
    {
        $this->updatedBy = $updatedBy;
    }

    /**
     * @return \DateTime
     */
    public function getDtValidity()
    {
        return $this->dtValidity;
    }

    /**
     * @param \DateTime $dtValidity
     */
    public function setDtValidity($dtValidity)
    {
        $this->dtValidity = $dtValidity;
    }

    /**
     * @return \Datetime
     */
    public function getDtExpiration()
    {
        return $this->dtExpiration;
    }

    /**
     * @param \Datetime $dtExpiration
     */
    public function setDtExpiration($dtExpiration)
    {
        $this->dtExpiration = $dtExpiration;
    }

    /**
     * @return string
     */
    public function getCreateMode()
    {
        return $this->createMode;
    }

    /**
     * @param string $createMode
     */
    public function setCreateMode($createMode)
    {
        $this->createMode = $createMode;
    }

    /**
     * @return string
     */
    public function getGt1()
    {
        return $this->gt1;
    }

    /**
     * @param string $gt1
     */
    public function setGt1($gt1)
    {
        $this->gt1 = $gt1;
    }

    /**
     * @return string
     */
    public function getGt3()
    {
        return $this->gt3;
    }

    /**
     * @param string $gt3
     */
    public function setGt3($gt3)
    {
        $this->gt3 = $gt3;
    }

    /**
     * @return string
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param string $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    public function toArray()
    {
        return get_object_vars($this);
    }

    /**
     * @return int
     */
    public function getWim()
    {
        return $this->wim;
    }

    /**
     * @param int $wim
     */
    public function setWim($wim)
    {
        $this->wim = $wim;
    }

    /**
     * @return int
     */
    public function getLeb()
    {
        return $this->leb;
    }

    /**
     * @param int $leb
     */
    public function setLeb($leb)
    {
        $this->leb = $leb;
    }

    /**
     * @return int
     */
    public function getAer()
    {
        return $this->aer;
    }

    /**
     * @param int $aer
     */
    public function setAer($aer)
    {
        $this->aer = $aer;
    }

    /**
     * @return int
     */
    public function getPow()
    {
        return $this->pow;
    }

    /**
     * @param int $pow
     */
    public function setPow($pow)
    {
        $this->pow = $pow;
    }

    /**
     * @return int
     */
    public function getMai()
    {
        return $this->mai;
    }

    /**
     * @param int $mai
     */
    public function setMai($mai)
    {
        $this->mai = $mai;
    }

    /**
     * @return int
     */
    public function getWol()
    {
        return $this->wol;
    }

    /**
     * @param int $wol
     */
    public function setWol($wol)
    {
        $this->wol = $wol;
    }
}
